<?php

namespace Application\Form;

use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

class UsuarioFilter implements InputFilterAwareInterface {

	protected $_inputFilter;

	public function getInputFilter(){
		if (! $this->_inputFilter) {
			$this->_inputFilter = new InputFilter();
			$factory = new InputFactory();

			/* criar filtros */
			$this->_inputFilter->add($factory->createInput(array(
				'name' => 'login',
				'required' => true,
				'filters' => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					array(
						'name' => 'StringLength',
						'options' => array(
							'encoding' => 'UTF-8',
							'min' => 3,
							'max' => 70
						)
					)
				)
			)));
		}
		return $this->_inputFilter;
	}

	public function setInputFilter(InputFilterInterface $inputFilter) {
		throw new \Exception("Method not necessary.");
	}
}
