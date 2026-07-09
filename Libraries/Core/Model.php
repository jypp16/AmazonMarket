<?php

namespace Libraries\Core;

use PDO;

class Model extends Conexion {

    protected $table;
    protected $primaryKey = 'id';

    protected $select = '*';
    protected $joins = [];
    protected $whereBuilder = [];
    protected $whereValues = [];

    protected $orderBy = '';
    protected $limit = '';
    protected $offset = '';

    private static $FORBIDDEN_SQL = [
        'UNION', 'SELECT.*FROM', '--', ';--', '/*', '*/', 'EXEC',
        'EXECUTE', 'DROP', 'DELETE', 'TRUNCATE', 'ALTER', 'CREATE',
        'INSERT', 'SLEEP', 'BENCHMARK', 'LOAD_FILE', 'INTO OUTFILE',
        'INTO DUMPFILE', 'INFORMATION_SCHEMA'
    ];

    public function __construct() {
        $this->conect = Conexion::getInstance()->conect();
    }

    private function validateIdentifier($name, $context = 'identifier'): string {
        $clean = preg_replace('/[^a-zA-Z0-9_\.]/', '', $name);
        if (empty($clean) || $clean !== $name) {
            throw new \InvalidArgumentException("Invalid $context: $name");
        }
        return $clean;
    }

    private function quoteFieldPath($field): string {
        $parts = explode('.', $field);
        $quoted = array_map(function($p) { return '`' . trim($p) . '`'; }, $parts);
        return implode('.', $quoted);
    }

    private function validateFieldName($field): string {
        $field = trim($field);
        if ($field === '*' || preg_match('/^[a-zA-Z0-9_]+\.\*$/', $field)) {
            return $field;
        }
        $clean = preg_replace('/[^a-zA-Z0-9_\. ]/', '', $field);
        $clean = preg_replace('/\s+/', ' ', $clean);
        if ($clean !== $field) {
            throw new \InvalidArgumentException("Invalid field: $field");
        }
        $upper = strtoupper($clean);
        if (preg_match('/\b(UNION|SELECT|FROM|WHERE|DROP|DELETE|INSERT|UPDATE|ALTER|CREATE)\b/', $upper)) {
            throw new \InvalidArgumentException("Field contains SQL keyword: $field");
        }
        return $clean;
    }

    private function validateDirection($dir): string {
        $dir = strtoupper(trim($dir));
        return ($dir === 'ASC' || $dir === 'DESC') ? $dir : 'ASC';
    }

    private function checkForSqlInjection($value) {
        $upper = strtoupper($value);
        foreach (self::$FORBIDDEN_SQL as $pattern) {
            if (strpos($upper, $pattern) !== false) {
                throw new \InvalidArgumentException("Potential SQL injection detected in: $value");
            }
        }
    }

    public function all() {
        $sql = "SELECT * FROM `$this->table` ORDER BY `$this->primaryKey` ASC";
        $stmt = $this->conect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $sql = "SELECT * FROM `$this->table` WHERE `$this->primaryKey` = :id";
        $stmt = $this->conect()->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $sql = "DELETE FROM `$this->table` WHERE `$this->primaryKey` = :id";
        $stmt = $this->conect()->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function insert($data) {
        $columns = implode(", ", array_map(function($k) { return $this->quoteFieldPath($k); }, array_keys($data)));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO `$this->table` ($columns) VALUES ($placeholders)";
        $stmt = $this->conect()->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        return $stmt->execute();
    }

    public function update($id, $data) {
        $setClause = "";
        foreach ($data as $key => $value) {
            $safeKey = $this->validateFieldName($key);
            $setClause .= $this->quoteFieldPath($safeKey) . " = :$safeKey, ";
        }
        $setClause = rtrim($setClause, ", ");
        $sql = "UPDATE `$this->table` SET $setClause WHERE `$this->primaryKey` = :id";
        $stmt = $this->conect()->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function where($conditions) {
        foreach($conditions as $key => $condition) {
            if (is_numeric($key)) {
                $this->checkForSqlInjection($condition);
                $this->whereBuilder[] = $condition;
            } else {
                $safeKey = preg_replace('/[^a-zA-Z0-9_]/', '_', $key);
                $this->whereBuilder[] = $this->quoteFieldPath($key) . " = :where_$safeKey";
                $this->whereValues[$safeKey] = $condition;
            }
        }
        return $this;
    }

    public function select($fields) {
        $validated = array_map([$this, 'validateFieldName'], $fields);
        $quoted = array_map(function($f) {
            if ($f === '*' || preg_match('/^[a-zA-Z0-9_]+\.\*$/', $f)) {
                return $f;
            }
            if (stripos($f, ' as ') !== false) {
                $parts = explode(' as ', $f, 2);
                return $this->quoteFieldPath(trim($parts[0])) . ' AS `' . trim($parts[1]) . '`';
            }
            return $this->quoteFieldPath($f);
        }, $validated);
        $this->select = implode(", ", $quoted);
        return $this;
    }

    public function join($table, $condition, $type = 'INNER') {
        $this->validateIdentifier($table, 'join table');
        $allowedTypes = ['INNER', 'LEFT', 'RIGHT', 'CROSS'];
        $type = strtoupper(trim($type));
        if (!in_array($type, $allowedTypes)) {
            throw new \InvalidArgumentException("Invalid JOIN type: $type");
        }
        $this->checkForSqlInjection($condition);
        $quotedCondition = preg_replace_callback('/([a-zA-Z_][a-zA-Z0-9_]*)\.([a-zA-Z_][a-zA-Z0-9_]*)/', function($m) {
            return '`' . $m[1] . '`.`' . $m[2] . '`';
        }, $condition);
        $this->joins[] = "$type JOIN `$table` ON $quotedCondition";
        return $this;
    }

    public function likeWhere(string $field, string $value): self {
        $safeField = $this->quoteFieldPath($field);
        $safeKey = preg_replace('/[^a-zA-Z0-9_]/', '_', $field);
        $this->whereBuilder[] = "$safeField LIKE :where_like_$safeKey";
        $this->whereValues["like_$safeKey"] = '%' . $value . '%';
        return $this;
    }

    public function orLikeWhere(array $fieldValuePairs, string $groupPrefix = ''): self {
        $parts = [];
        foreach ($fieldValuePairs as $field => $value) {
            $safeField = $this->quoteFieldPath($field);
            $safeKey = $groupPrefix . preg_replace('/[^a-zA-Z0-9_]/', '_', $field);
            $parts[] = "$safeField LIKE :where_like_$safeKey";
            $this->whereValues["like_$safeKey"] = '%' . $value . '%';
        }
        $this->whereBuilder[] = '(' . implode(' OR ', $parts) . ')';
        return $this;
    }

    public function orderBy($field, $direction = 'ASC') {
        $field = $this->validateFieldName($field);
        $direction = $this->validateDirection($direction);
        $this->orderBy = "ORDER BY " . $this->quoteFieldPath($field) . " $direction";
        return $this;
    }

    public function limit($limit) {
        $limit = intval($limit);
        $this->limit = "LIMIT $limit";
        return $this;
    }

    public function offset($offset) {
        $offset = intval($offset);
        $this->offset = "OFFSET $offset";
        return $this;
    }

    public function get() {
        $sql = "SELECT $this->select FROM `$this->table` ";
        if (!empty($this->joins)) {
            $sql .= implode(" ", $this->joins) . " ";
        }
        if (!empty($this->whereBuilder)) {
            $sql .= "WHERE " . implode(" AND ", $this->whereBuilder) . " ";
        }
        if (!empty($this->orderBy)) {
            $sql .= $this->orderBy . " ";
        } else {
            $sql .= "ORDER BY " . $this->quoteFieldPath($this->table . "." . $this->primaryKey) . " ASC ";
        }
        if (!empty($this->limit)) {
            $sql .= $this->limit . " ";
        }
        if (!empty($this->offset)) {
            $sql .= $this->offset;
        }
        $stmt = $this->conect()->prepare($sql);
        foreach ($this->whereValues as $key => $value) {
            $stmt->bindValue(":where_$key", $value);
        }
        $stmt->execute();
        $this->resetQuery();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function resetQuery() {
        $this->select = '*';
        $this->joins = [];
        $this->whereBuilder = [];
        $this->whereValues = [];
        $this->orderBy = '';
        $this->limit = '';
        $this->offset = '';
    }

    public function first() {
        $this->limit(1);
        $result = $this->get();
        return $result[0] ?? null;
    }

    public function count($condition = '') {
        $sql = "SELECT COUNT(*) FROM `$this->table`";
        if (!empty($condition)) {
            $this->checkForSqlInjection($condition);
            $sql .= " WHERE $condition";
        }
        $stmt = $this->conect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function countWithQuery() {
        $sql = "SELECT COUNT(*) as total FROM `$this->table` ";
        if (!empty($this->joins)) {
            $sql .= implode(" ", $this->joins) . " ";
        }
        if (!empty($this->whereBuilder)) {
            $sql .= "WHERE " . implode(" AND ", $this->whereBuilder) . " ";
        }
        $stmt = $this->conect()->prepare($sql);
        foreach ($this->whereValues as $key => $value) {
            $stmt->bindValue(":where_$key", $value);
        }
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function sum($field, $condition = '') {
        $field = $this->validateFieldName($field);
        $sql = "SELECT SUM(" . $this->quoteFieldPath($field) . ") FROM `$this->table`";
        if (!empty($condition)) {
            $this->checkForSqlInjection($condition);
            $sql .= " WHERE $condition";
        }
        $stmt = $this->conect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn() ?: 0.00;
    }

    public function getTable() {
        return $this->table;
    }

    public function getPrimaryKey() {
        return $this->primaryKey;
    }
}
