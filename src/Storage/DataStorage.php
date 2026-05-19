<?php

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
         * Medium Severity: No DI, cause hard to test
         */
        $this->pdo = new \PDO('mysql:dbname=task_tracker;host=127.0.0.1', 'user');
    }

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

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return new Model\Project($row);
        }

        throw new Model\NotFoundException();
    }

    /**
     * @param int $project_id
     * @param int $limit
     * @param int $offset
     */
    public function getTasksByProjectId(int $project_id, $limit, $offset)
    {
        /**
         * Critical Severity: SQL injection vulnerability (Ex: project_id = "1 OR 1=1"). Should prepare data (sanitization) before pass into query.
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
         * Many issues here:
         * 
         * 1. Critical Severity: SQL injection vulnerability (Ex: $data['title'] = 'abc", 1); DROP TABLE task; --'; ) 
         * 2. Medium Severity: SELECT MAX(id) could be Race condition if many query execute at the same time.
         * 3. Low Severity: No escape special characters
         */
        $this->pdo->query("INSERT INTO task ($fields) VALUES ($values)");
        $data['id'] = $this->pdo->query('SELECT MAX(id) FROM task')->fetchColumn();

        return new Model\Task($data);
    }
}
