<?php

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
     * @param Request $request
     * 
     * @Route("/project/{id}", name="project", method="GET")
     */
    public function projectAction(Request $request)
    {
        try {
            /**
             * Issues:
             * 1. Medium Severity: Missing validation for id, id existence 
             * 2. High Severity: no authorization, anyone can see any projects
             */
            $project = $this->storage->getProjectById($request->get('id'));

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
     * @param Request $request
     *
     * @Route("/project/{id}/tasks", name="project-tasks", method="GET")
     */
    public function projectTaskPagerAction(Request $request)
    {
        /**
         * Issues:
         * 1. Medium Severity: Missing validation for id, limit and offset. 
         * Validation should include: type, and range value. Also, should validate "id" existence
         * 2. High Severity: no authorization, anyone can see any tasks of any projects
         */
        $tasks = $this->storage->getTasksByProjectId(
            $request->get('id'),
            $request->get('limit'),
            $request->get('offset')
        );

        return new Response(json_encode($tasks));
    }

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
     * 2. Missing proper http status codes or custom code when return
     */
}
