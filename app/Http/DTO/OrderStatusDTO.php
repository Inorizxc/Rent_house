<?php
class TicketDTO
{
    public function __construct(
        private string $order_status_id,
        private string $type,
    ) {}
    public function __get(string $property): mixed
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        
        throw new InvalidArgumentException("Свойство $property не существует");
    }
    public function __set(string $property, mixed $value): void
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        } else {
            throw new InvalidArgumentException("Свойство $property не существует");
        }
    }
    public function __isset(string $property): bool
    {
        return property_exists($this, $property);
    }
    // Add getters, setters, validation
}