<?php

class CourseService
{
    private CourseRepository $repo;

    public function __construct(CourseRepository $repo)
    {
        $this->repo = $repo;
    }

    public function loadChildPendingCourses(string $childId): array
    {
        return $this->repo->getChildPendingCourses($childId);
    }

    public function loadChildPickCourses(string $childId): array
    {
        return $this->repo->getChildPickCourses($childId);
    }

    public function loadAvailableCourses(string $childId): array
    {
        return $this->repo->getAvailableCourses($childId);
    }

    public function pickCourse(string $childId, string $courseCode): array
    {
    	$this->repo->pickCourse($childId, $courseCode);
        return ["message" => "Course picked successfully"];
    }

    public function removeCourse(string $childId, string $courseCode): array
    {
    	$this->repo->removeCourse($childId, $courseCode);
        return ["message" => "Course deleted successfully"];
    }

    public function payCourse(string $childId, string $courseCode, float $amount, string $parentId): array
    {
        $this->repo->payCourse($childId, $courseCode, $amount, $parentId);
        return ["message" => "Course paid successfully"];
    }
}
