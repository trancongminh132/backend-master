<?php

/**
 * Recommend: add declare(strict_types=1); at the beginning of the file
 */

namespace Api\Controller;

use App\Model;
use App\Storage\DataStorage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController 
{
    /**
     * @var DataStorage
     */
    private $storage;

    public function __construct(DataStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Low Severity: Missing @return
     */
    /**
     * @param Request $request
     * 
     * @Route("/project/{id}", name="project", method="GET")
     */
    public function projectAction(Request $request)
    {
        try {
            /**
             * Issues:
             * 1. Medium Severity: Missing validation for id (int, existence)
             * 2. High Severity: no authorization, anyone can see any projects
             */
            $project = $this->storage->getProjectById($request->get('id'));

            /**
             * Medium Severity:
             * With code below, seem double encoded JSON because JsonResponse already calls json_encode() internally
             */
            return new Response($project->toJson());
        } catch (Model\NotFoundException $e) {
            return new Response('Not found', 404);
        } catch (\Throwable $e) {
            /**
             * Low Severity: Missing log the error details to debug
             */
            return new Response('Something went wrong', 500);
        }
    }

    /**
     * Low Severity: Missing @return
     */
    /**
     * @param Request $request
     *
     * @Route("/project/{id}/tasks", name="project-tasks", method="GET")
     */
    public function projectTaskPagerAction(Request $request)
    {
        /**
         * Issues:
         * 1. Medium Severity: Missing validation for
         *   ++ id: int, project existence
         *   ++ limit: int, max-min range
         *   ++ offset: int, max-min range, > 0 
         * Validation should include: type, and range value. Also, should validate "id" existence
         * 2. High Severity: no authorization, anyone can see any tasks of any projects
         */
        $tasks = $this->storage->getTasksByProjectId(
            $request->get('id'),
            $request->get('limit'),
            $request->get('offset')
        );

        /**
         * Medium Severity: tasks is array of object, could not encode
         * Change to "return new JsonResponse($tasks);"
         *
         */
        return new Response(json_encode($tasks));
    }

    /**
     * Low Severity: Missing @return
     */
    /**
     * @param Request $request
     *
     * @Route("/project/{id}/tasks", name="project-create-task", method="PUT")
     */
    public function projectCreateTaskAction(Request $request)
    {
        /**
         * Issues:
         * 1. Medium Severity: Missing validation for id
         * 2. High Severity: no authorization, then all user can create task on any project
         */
		$project = $this->storage->getProjectById($request->get('id'));
		if (!$project) {
			return new JsonResponse(['error' => 'Not found']);
		}
		
        /**
         * Issues:
         * 1. High Severity: Mass assignment vulnerability when pass $_REQUEST (whole data) into method
         * 2. High Severity: Missing validation for request data
         * 3. Low Severity: bad practice when use $_REQUEST which bypass framework abstraction, hard tro test
         */
		return new JsonResponse(
			$this->storage->createTask($_REQUEST, $project->getId())
		);
    }

    /**
     * Beside that, there are some minor issues:
     * 1. Return should be consistent as some methods return JsonResponse, some methods return Response
     * 2. Some method missing return proper http status codes or system custom code
     */
}
