<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Tefu
 * Date: 7/29/13
 * Time: 11:24 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Album\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Album\Model\Album; // <-- Add this import
use Album\Form\AlbumForm; // <-- Add this import


class AlbumController extends AbstractActionController
{

    protected $albumTable;

    public function getAlbumTable()
    {
        if (!$this->albumTable) {
            $sm               = $this->getServiceLocator(); // ?
            $this->albumTable = $sm->get('Album\Model\AlbumTable');
        }
        return $this->albumTable;
    }

    /*
     * You should also add:

       protected $albumTable;

     * to the top of the class.
     * We can now call getAlbumTable() from within our controller whenever we need to interact with our model.
     * If the service locator was configured correctly in Module.php,
     * then we should get an instance of Album\Model\AlbumTable when calling getAlbumTable().
     */


    public function indexAction()
    { // ?
        return new ViewModel(array(
            'albums' => $this->getAlbumTable()->fetchAll(),
        ));
    }

    public function addAction()
    {
        $form = new AlbumForm();
        $form->get('submit')->setValue('Add');
        /* We instantiate AlbumForm and set the label on the submit button to “Add”.
         * We do this here as we’ll want to re-use the form when editing an album and
         * will use a different label.
         */


        $request = $this->getRequest();
        if ($request->isPost()) {
            $album = new Album();
            $form->setInputFilter($album->getInputFilter());
            $form->setData($request->getPost());
            /* If the Request object’s isPost() method is true,
             * then the form has been submitted
             * and so we set the form’s input filter from an album instance.
             * We then set the posted data to the form
             * and check to see if it is valid using the isValid() member function of the form.
             */

            if ($form->isValid()) {
                $album->exchangeArray($form->getData());
                $this->getAlbumTable()->saveAlbum($album);
                /* If the form is valid, then we grab the data from the form and store to the model using saveAlbum().*/

                // Redirect to list of albums
                return $this->redirect()->toRoute('album');
            }
        }

        return array('form' => $form);
        /*
         * Finally, we return the variables that we want assigned to the view.
         * In this case, just the form object.
         * Note that Zend Framework 2 also allows you to simply return an array containing the variables to be assigned
         * to the view
         * and it will create a ViewModel behind the scenes for you. This saves a little typing.
         */
    }

    public function editAction()
    {

        $id = (int)$this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('album', array(
                'action' => 'add'
            ));
        }
        /* ????????
         * params is a controller plugin that provides a convenient way to retrieve parameters from the matched route.
         * We use it to retrieve the id from the route we created in the modules’ module.config.php.
         * If the id is zero, then we redirect to the add action,
         * otherwise, we continue by getting the album entity from the database.
         */

        // 根据id取Album实体，
        try {
            $album = $this->getAlbumTable()->getAlbum($id);

        } catch (\Exception $ex) {
            return $this->redirect()->toRoute('album', array('action' => 'index'));
        }
        /*
         * We have to check to make sure that the Album with the specified id can actually be found.
         * If it cannot, then the data access method throws an exception.
         * We catch that exception and re-route the user to the index page.
         */


        // 将Album数据bind到form, 修改'submit'的attribute 'value' 的值为'Edit' （因为这里一表两用，同时用作add和edit）
        $form = new AlbumForm();
        $form->bind($album);
        $form->get('submit')->setAttribute('value', 'Edit');
        /*
         * The form’s bind() method attaches the model to the form. This is used in two ways:
         * 1. When displaying the form, the initial values for each element are extracted from the model.这里是第一种？
         * 2. After successful validation in isValid(), the data from the form is put back into the model.
         */

        /*
         * As a result of using bind() with its hydrator, we do not need to populate the form’s data
         * back into the $album as that’s already been done,
         * so we can just call the mappers’ saveAlbum() to store the changes back to the database.
         */


        // if 取得数据 (save to database), then redirect to index page
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($album->getInputFilter());
            $form->setData($request->getPost());

            // 取得数据后 进行验证isValid()
            if ($form->isValid()) {
                $this->getAlbumTable()->saveAlbum($form->getData());

                // redirect to index page
                return $this->redirect()->toRoute('album');
            }

        }

        // return id and form (通过url进入editAction，返回id和form内容给对应的edit view)
        return array(
            'id' => $id,
            'form' => $form,
        );

    }

    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('album');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int)$request->getPost('id');
                $this->getAlbumTable()->deleteAlbum($id);
            }

            // Redirect to list of albums
            return $this->redirect()->toRoute('album');
        }

        return array(
            'id' => $id,
            'album' => $this->getAlbumTable()->getAlbum($id)
        );

        /*
         * As before, we get the id from the matched route,
         * and check the request object’s isPost() to determine
         * whether to show the confirmation page or to delete the album.
         * We use the table object to delete the row using the deleteAlbum() method
         * and then redirect back the list of albums.
         * If the request is not a POST,
         * then we retrieve the correct database record and assign to the view, along with the id.
         */
    }

}

