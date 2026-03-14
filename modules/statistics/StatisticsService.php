<?php

class StatisticsService
{
    private StatisticsRepository $repo;

    public function __construct(StatisticsRepository $repo)
    {
        $this->repo = $repo;
    }

    public function loadVisitedCourses(string $childId): array
    {
        return $this->repo->loadVisitedCourses($childId);
    }
}