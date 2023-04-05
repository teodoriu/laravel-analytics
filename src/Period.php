<?php

namespace ErlanCarreira\Analytics;

use Carbon\Carbon;
use DateTimeInterface;
use ErlanCarreira\Analytics\Exceptions\InvalidPeriod;

class Period
{
    /** @var \DateTimeInterface */
    public $startDate;

    /** @var \DateTimeInterface */
    public $endDate;

    public static function create(DateTimeInterface $startDate, DateTimeInterface $endDate): self
    {
        return new static($startDate, $endDate);
    }

    public static function days(int $numberOfDays): self
    {
        $endDate = Carbon::yesterday();

        $startDate = Carbon::today()->subDays($numberOfDays)->startOfDay();

        return new static($startDate, $endDate);
    }

    public static function months(int $numberOfMonths): self
    {
        $endDate = Carbon::yesterday();

        $startDate = Carbon::today()->subMonths($numberOfMonths)->startOfDay();

        return new static($startDate, $endDate);
    }

    public static function years(int $numberOfYears): self
    {
        $endDate = Carbon::yesterday();

        $startDate = Carbon::today()->subYears($numberOfYears)->startOfDay();

        return new static($startDate, $endDate);
    }

    public function __construct(DateTimeInterface $startDate, DateTimeInterface $endDate)
    {
        if ($startDate > $endDate) {
            throw InvalidPeriod::startDateCannotBeAfterEndDate($startDate, $endDate);
        }

        $this->startDate = $startDate;

        $this->endDate = $endDate;
    }
}
