<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class PostsController extends ControllerBase
{
    /**
     * Index action
     */
    public function indexAction()
    {
        $this->persistent->parameters = null;
    }

    /**
     * Searches for posts
     */
    public function searchAction()
    {
        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Posts', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "id";

        $posts = Posts::find($parameters);
        if (count($posts) == 0) {
            $this->flash->error("No se encontraron resultados");

            $this->dispatcher->forward([
                "controller" => "posts",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $posts,
            'limit'=> 10,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();
    }

    /**
     * Displays the creation form
     */
    public function newAction()
    {

    }

    /**
     * Edits a post
     *
     * @param string $id
     */
    public function editAction($id)
    {
        if (!$this->request->isPost()) {

            $post = Posts::findFirstByid($id);
            if (!$post) {
                $this->flash->error("post was not found");

                $this->dispatcher->forward([
                    'controller' => "posts",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->id = $post->id;

            $this->tag->setDefault("id", $post->id);
            $this->tag->setDefault("title", $post->title);
            $this->tag->setDefault("content", $post->content);
            $this->tag->setDefault("created_at", $post->created_at);
            
        }
    }

    /**
     * Creates a new post
     */
    public function createAction()
    {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "posts",
                'action' => 'index'
            ]);

            return;
        }

        $post = new Posts();
        $post->title = $this->request->getPost("title");
        $post->content = $this->request->getPost("content");
        $post->created_at = $this->request->getPost("created_at");
        

        if (!$post->save()) {
            foreach ($post->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "posts",
                'action' => 'new'
            ]);

            return;
        }

        $this->flash->success("Posts creado satisfactoriamente.");

        $this->dispatcher->forward([
            'controller' => "posts",
            'action' => 'index'
        ]);
    }

    /**
     * Saves a post edited
     *
     */
    public function saveAction()
    {

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "posts",
                'action' => 'index'
            ]);

            return;
        }

        $id = $this->request->getPost("id");
        $post = Posts::findFirstByid($id);

        if (!$post) {
            $this->flash->error("post does not exist " . $id);

            $this->dispatcher->forward([
                'controller' => "posts",
                'action' => 'index'
            ]);

            return;
        }

        $post->title = $this->request->getPost("title");
        $post->content = $this->request->getPost("content");
        $post->created_at = $this->request->getPost("created_at");
        

        if (!$post->save()) {

            foreach ($post->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "posts",
                'action' => 'edit',
                'params' => [$post->id]
            ]);

            return;
        }

        $this->flash->success("El post ha sido actualizado correctamente");

        $this->dispatcher->forward([
            'controller' => "posts",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a post
     *
     * @param string $id
     */
    public function deleteAction($id)
    {
        $post = Posts::findFirstByid($id);
        if (!$post) {
            $this->flash->error("post was not found");

            $this->dispatcher->forward([
                'controller' => "posts",
                'action' => 'index'
            ]);

            return;
        }

        if (!$post->delete()) {

            foreach ($post->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "posts",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("El posts ha sido eliminado correctamente");

        $this->dispatcher->forward([
            'controller' => "posts",
            'action' => "index"
        ]);
    }

}
