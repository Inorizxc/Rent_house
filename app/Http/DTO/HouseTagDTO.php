<?php

class HouseTagDTO
{
    public function __construct(
        private string $house_tag_id,
        private string $house_id,
        private string $tag_id,
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
