<?php
class UserDTO
{
    public function __construct(
        private string $user_id,
        private string $role_id,
        private string $name,
        private string $sename,
        private string $patronymic,
        private string $birth_date,
        private string $email,
        private string $password,
        private string $phone,
        private string $card,
        private string $need_verification,
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