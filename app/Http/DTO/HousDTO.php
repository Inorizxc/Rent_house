<?php

class HouseDTO
{
    public function __construct(
        private string $house_id,
        private string $user_id,
        private string $price_id,
        private string $rent_type_id,
        private string $house_type_id,
        private string $house_calendar_id,
        private string $adress,
        private string $area,
        private string $is_deleted,
        private string $lng,
        private string $ltd,
    ) {}
    
    public function get(){
        return [
        'house_id'=>$this->house_id,
        'user_id'=>$this->user_id,
        'price_id'=>$this->price_id,   
        'rent_type_id'=>$this->rent_type_id,
        'house_type_id'=>$this->house_type_id,
        'house_calendar_id'=>$this->house_calendar_id,
        'adress'=>$this->house_id,
        'area'=>$this->house_id,
        'is_deleted'=>$this->house_id,
        'lng'=>$this->house_id,
        'lat'=>$this->house_id,


        ];
    }

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
