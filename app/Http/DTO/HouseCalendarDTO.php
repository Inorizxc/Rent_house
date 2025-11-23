<?php

class HouseCalendarDTO
{
    public function __construct(
        private string $house_calendar_id,
        private string $house_id,
        private string $first_date,
        private string $second_date,
    ) {}

    public function __get(string $property): mixed
    {
        if(property_exists($this,$property)){
            return $this->$property;
        }
        throw new InvalidArgumentException("");
    }

    public function __set(string $property, string $value)
    {
        if(property_exists($this,$property)){
            $this->$property = $value;
        }
        throw new InvalidArgumentException("");
    }

    public function isset(string $property){

        return property_exists($this,$property);
    }
}
