<?php

namespace Levaral\Core\Mapper;

use Carbon\Carbon;
use Illuminate\Validation\ValidationRuleParser;
use Illuminate\Validation\Validator;

trait MappingTrait
{
    /**
     * Extracts validated data from the request and coerces values to the type specified in the validation rules.
     *
     * @return array
     */
    public function data()
    {
        /** @var Validator $validator */
        $validator = $this->getValidatorInstance();
        $validationData = $this->request->all();
        $validRules = [];

        foreach ($validator->getRules() as $attribute => $rules) {
            if ($validator->getMessageBag()->has($attribute)) {
                array_forget($validationData, $attribute);
                continue;
            }
            $validRules[$attribute] = $rules;
        }

        $data = [];
        foreach ($validRules as $attribute => $rules) {
            if (!array_has($validationData, $attribute)) {
                continue;
            }

            $type = null;
            foreach ($rules as $rule) {
                list($rule) = ValidationRuleParser::parse($rule);

                if ($rule == '') {
                    continue;
                }

                if (in_array($rule, ['Integer', 'String', 'Date', 'Boolean', 'Accepted', 'Double'])) {
                    $type = $rule;
                    break;
                }
            }

            $value = array_get($validationData, $attribute);

            if (is_null($value)) {
                array_set($data, $attribute, null);
                continue;
            }

            switch ($type) {
                case 'Integer':
                    $value = intval($value);
                    break;
                case 'Double':
                    $value = doubleval($value);
                    break;
                case 'Date':
                    $value = Carbon::createFromTimestamp(strtotime($value));
                    break;
                case 'Boolean':
                    $value = in_array($value, [true, 1, '1'], true);
                    break;
                case 'Accepted':
                    $value = true;
                    break;
                default:
                    break;
            }

            array_set($data, $attribute, $value);
        }

        return $data;
    }

    /**
     * Auto maps the given data (or mappingData frpom request) to the given object.
     *
     * @param $object
     * @param array|null $mappingData
     * @return mixed
     */
    public function mapData($object, array $mappingData = null)
    {
        $mappingData = $mappingData ?? $this->data();
        return AutoMapper::mapData($object, $mappingData);
    }
}