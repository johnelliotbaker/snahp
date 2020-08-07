<?php

namespace jeb\snahp\core\Rest\Serializers;

require_once 'ext/jeb/snahp/core/Rest/Utils.php';



class Serializer
{
    protected $model;
    public $fields;
    public $serializedData;


    public function __construct($instance, $data, $kwargs=[])
    {
        $this->instance = $instance;
        if ($data !== null) {
            $this->initialData = $data;
        }
    }

    public function isValid()
    {
        if (!property_exists($this, 'initialData')) {
            trigger_error(
                'Cannot call isValid() as no data= keyword argument ' .
                ' was passed when instantialing the serializer instance.'
            );
        }

        if (!property_exists($this, '_validatedData')) {
            try {
                $this->_validatedData = $this->runValidation($this->initialData);
            } catch (Exception $e) {
                $this->_validatedData = [];
                $this->_errors = "Error Code: 99c6bbb563";
            }
            $this->_errors = [];
        }
        return !$this->_errors;
    }

    public function runValidation($initialData=null)
    {
        if ($initialData===null) {
            trigger_error('$data cannot be null.');
        }
        return $this->validate($initialData);
    }

    public function validate($initialData)
    {
        $fields = $this->model->getFields();
        $keys = array_filter(
            array_keys($initialData),
            function ($key) use ($fields) {
                return array_key_exists($key, $fields);
            }
        );
        foreach ($keys as $key) {
            $klass = $fields[$key];
            $validatedData[$key] = $klass->validate($initialData[$key]);
        }
        return $validatedData;
    }

    public function serialize()
    {
        $validatedData = $this->validatedData();
        $serializedData = [];
        foreach ($this->model->getFields() as $name => $klassname) {
            $serializedData[$name] = $klassname::serialize($validatedData[$name]);
        }
        $this->serializedData = $serializedData;
        return $serializedData;
    }

    public function save($newData=[])
    {
        if (!property_exists($this, '_errors')) {
            trigger_error("You must call isValid() before calling save()");
        }

        $validatedData = array_merge($this->validatedData(), $newData);
        if ($this->instance) {
            $this->instance = $this->update($this->instance, $validatedData);
            if (!$this->instance) {
                trigger_error(".update() did not return an object instance.");
            }
        } else {
            $this->instance = $this->create($validatedData);
            if (!$this->instance) {
                trigger_error(".create() did not return an object instance.");
            }
        }
        return $this->instance;
    }

    public function create($validatedData)
    {
        return $this->model->create($validatedData);
    }

    public function update($instance, $validatedData)
    {
        return $this->model->update($instance, $validatedData);
    }

    public function validatedData()
    {
        if (!property_exists($this, '_validatedData')) {
            trigger_error("You must call isValid() before accessing validatedData.");
        }
        return $this->_validatedData;
    }

    public function data()
    {
        return $this->instance;
    }
}