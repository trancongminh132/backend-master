<?php

/**
 * Recommend: add declare(strict_types=1); at the beginning of the file
 */

namespace App\Storage;

use App\Model;

class DataStorage
{
    /**
     * @var \PDO 
     */
    /**
     * Low Severity: 
     * 1. $pdo scope should be private/protected to encapsulate db access
     * 2. Missing type hints
     * 
     */
    public $pdo;

    public function __construct()
    {
        /**
         * High Severity: hardcode db credentials, should store in environment variables or config files 
         */
        /**
         * Medium Severity: Missing dependency injection, cause hard to test
         */
        $this->pdo = new \PDO('mysql:dbname=task_tracker;host=127.0.0.1', 'user');
    }

    /**
     * Low Severity: 
     * 1. Missing type hint for $projectId
     * 2. Missing @return
     */
    /**
     * @param int $projectId
     * @throws Model\NotFoundException
     */
    public function getProjectById($projectId)
    {
        /**
         * As converted to int for $projectId then no sql injection here but 
         * Should prepare data (sanitization) before pass into query.
         */
        $stmt = $this->pdo->query('SELECT * FROM project WHERE id = ' . (int) $projectId);

        /**
         * for cleaner, we can do like:
         * $row = $stmt->fetch(PDO::FETCH_ASSOC);
         * if (!$row) {
         *    throw new Model\NotFoundException();
         * }
         * 
         * return new Model\Project($row);
         */
        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return new Model\Project($row);
        }

        throw new Model\NotFoundException();
    }

    /**
     * Low Severity: 
     * 1. Missing @return
     * 2. Missing type hint for $limit and $offset
     */
    /**
     * @param int $project_id
     * @param int $limit
     * @param int $offset
     */
    public function getTasksByProjectId(int $project_id, $limit, $offset)
    {
        /**
         * Critical Severity: 
         * 1. SQL injection vulnerability (Ex: project_id = "1 OR 1=1")
         * 2. query() executes immediately while placeholders (?) only work with prepare()
         */
        $stmt = $this->pdo->query("SELECT * FROM task WHERE project_id = $project_id LIMIT ?, ?");
        $stmt->execute([$limit, $offset]);

        $tasks = [];
        foreach ($stmt->fetchAll() as $row) {
            $tasks[] = new Model\Task($row);
        }

        return $tasks;
    }

    /**
     * Low Severity: Missing type hint for $projectId
     */
    /**
     * @param array $data
     * @param int $projectId
     * @return Model\Task
     */
    public function createTask(array $data, $projectId)
    {
        $data['project_id'] = $projectId;

        $fields = implode(',', array_keys($data));
        $values = implode(',', array_map(function ($v) {
            return is_string($v) ? '"' . $v . '"' : $v;
        }, $data));

        /**
         * Issues:
         * 1. Critical Severity: SQL injection vulnerability (Ex: $data['title'] = 'abc", 1); DROP TABLE task; --'; ) 
         * 2. Medium Severity: SELECT MAX(id) could be Race condition if many query execute at the same time.
         * 3. Low Severity: No escape special characters
         */
        $this->pdo->query("INSERT INTO task ($fields) VALUES ($values)");
        $data['id'] = $this->pdo->query('SELECT MAX(id) FROM task')->fetchColumn();

        return new Model\Task($data);
    }
}
