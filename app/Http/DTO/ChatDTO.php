<?php

class ChatDTO
{
    public function __construct(
        private string $chat_id,
        private string $user_id,
        private string $rent_dealer_id,
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
