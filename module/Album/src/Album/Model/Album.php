<?php
namespace Album\Model;

// Add these import statements
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Album implements InputFilterAwareInterface
{
    public $id;
    public $artist;
    public $title;
    protected $inputFilter; // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id     = (isset($data['id'])) ? $data['id'] : null;
        $this->artist = (isset($data['artist'])) ? $data['artist'] : null;
        $this->title  = (isset($data['title'])) ? $data['title'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
    /*
     * The form’s bind() method attaches the model to the form. This is used in two ways:
     * 1. When displaying the form, the initial values for each element are extracted from the model.
     * 2. After successful validation in isValid(), the data from the form is put back into the model.
     *
     * These operations are done using a hydrator object.
     * There are a number of hydrators, but the default one is Zend\Stdlib\Hydrator\ArraySerializable
     * which expects to find two methods in the model: getArrayCopy() and exchangeArray().
     * We have already written exchangeArray() in our Album entity, so just need to write getArrayCopy()
     */




    /*
     * We also need to set up validation for this form.
     * In Zend Framework 2 this is done using an input filter,
     * which can either be standalone or defined within any class
     * that implements the InputFilterAwareInterface interface, such as a model entity.
     * In our case, we are going to add the input filter to the Album class,
     * which resides in the Album.php file in module/Album/src/Album/Model
     */

    /*
     * The InputFilterAwareInterface defines two methods: setInputFilter() and getInputFilter().
     * We only need to implement getInputFilter() so we simply throw an exception in setInputFilter().
     */

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    /*
     * Within getInputFilter(), we instantiate an InputFilter and then add the inputs that we require.
     * We add one input for each property that we wish to filter or validate.
     * For the id field we add an Int filter as we only need integers.
     * For the text elements, we add two filters, StripTags and StringTrim,
     *      to remove unwanted HTML and unnecessary white space.
     * We also set them to be required and add a StringLength validator
     *      to ensure that the user doesn’t enter more characters than we can store into the database.
     */

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name' => 'id',
                'required' => true,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'artist',
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
                            'min' => 1,
                            'max' => 100,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'title',
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
                            'min' => 1,
                            'max' => 100,
                        ),
                    ),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}