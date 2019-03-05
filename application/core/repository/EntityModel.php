<?php

namespace core\repository;

abstract class EntityModel
{
    /**
     * @var array
     */
    private $attributes = [];

    public function loadingData(array $data): void
    {
        foreach ($data as $property => $val){

            if(is_array($val)){
                continue;
            }

            $this->checkPropertyRule($property);
            $this->attributes[$property] = $val;
        }
    }

    public function __get($name)
    {
        $this->checkPropertyRule($name);
        return $this->attributes[$name] ?? null;
    }

    public function __set($name, $value): void
    {
        $this->checkPropertyRule($name);
        $this->attributes[$name] = $value;
    }

    private function trace($text, $name): void
    {
        $trace = debug_backtrace();
        $in_file = $trace[1]['file'];
        $in_line = $trace[1]['line'];
        trigger_error(
            "
                <p>$text: <b>\"$name\"</b> on line <b>$in_line</b></p>
                <p>in $in_file</p>
                ",
            E_USER_NOTICE);
        exit;
    }

    private function checkPropertyRule(string $propertyName): bool
    {
        if(!method_exists($this, 'get' . ucfirst($propertyName))){
            $this->trace("Undefined property", $propertyName);
        }

        return true;
    }

    public function getProperties(): array
    {
        $properties = [];
        foreach ($this->attributes as $propertyName => $val){
            preg_match_all(
                '/[A-Z][^A-Z]*?/Usu',
                ucfirst($propertyName),
                $incompletePropertyName
            );

            $propertyNameInTable = strtolower(implode('_', $incompletePropertyName[0]));
            $properties[$propertyNameInTable] = $val;
        }

        return $properties;
    }
}