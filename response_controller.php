<?php
class ResponseController extends AppController {
    
    public $uses = null;
    
    public function beforeFilter(){
        $this->modelName = false;
        $this->data = $this->status = $this->message = false;
        if (!empty($_POST['data'])){
            $this->data = $_POST['data'];
        }
        if (!empty($this->params['model'])){
            $this->modelName = $this->params['model'];
            if ($this->action != 'schema'){
                $this->loadModel($this->modelName);
            }
        }
    }
    
    public function index(){
        $conditions = array();
        if ($this->modelName){
            if (!empty($this->params['named'])){
                $conditions = $this->params['named'];
            }
            $this->data = $this->{$this->modelName}->find('all',
                array(
                    'conditions'=>$conditions
                )
            );
            $output = array();
            foreach($this->data as $data){
                $output[] = $data[$this->modelName];
            }
            $this->data = $output;
            $this->status = 1;
            $this->message = 'Successfully queried model: ' . $this->modelName;
        }else{
            $this->status = 0;
            $this->message = 'No model specified';
        }
        $this->response = array(
            'status'=>$this->status,
            'message'=>$this->message,
            'data'=>$this->data
        );
    }
    
    public function add(){
        if (isset($this->data)){
            $model = $this->modelName;
            $data['Task'] = $this->data;
            $this->{$model}->create();
            if ($this->{$model}->save($data)){
                $this->data['id'] = $this->{$model}->id;
                $this->status = 1;
                $this->message = 'Record created for model: ' . $model;
            }else{
                $this->status = 0;
                $this->message = 'Record could not be created.  Failed validation';
            }
        }else{
            $this->status = 0;
            $this->message = 'No data received';
        }
        $this->response = array(
            'status'=>$this->status,
            'message'=>$this->message,
            'data'=>$this->data
        );
    }
    
    public function edit(){
        if (!empty($this->data)){
            $model = $this->modelName;
            if (!empty($this->data['_destroy'])){
                $this->{$model}->delete($this->data['id']);
                $this->status = 1;
                $this->message = 'Record ' . $this->data['id'] . ' deleted for model: ' . $model;
            }else{
                $this->data = $this->{$model}->getRelated($this->data);
                print_r($this->data);
                if ($this->{$model}->saveAll($this->data)){
                    $this->status = 1;
                    $this->message = 'Record ' . $this->data['id'] . ' updated for model: ' . $model;
                }else{
                    $this->status = 0;
                    $this->message = 'Record ' . $this->data['id'] . ' could not be updated.  Failed validation';
                }
            }
        }else{
            $this->status = 0;
            $this->message = 'No data received';
        }
        $this->response = array(
            'status'=>$this->status,
            'message'=>$this->message,
            'data'=>$this->data
        );
    }
    
    public function delete($id){
        if (!empty($id)){
            $model = $this->modelName;
            if ($this->{$model}->delete($id)){
                $this->status = 1;
                $this->message = 'Record ' . $id . ' deleted from model: ' . $model;
            }else{
                $this->status = 0;
                $this->message = 'Record ' . $id . ' could not be deleted.';
            }
        }else{
            $this->status = 0;
            $this->message = 'No ID supplied.';
        }
        $this->response = array(
            'status'=>$this->status,
            'message'=>$this->message,
            'data'=>array('id'=>$id)
        );
    }
    
    public function updateAll(){
        if (!empty($this->data)){
            $model = $this->modelName;
            foreach($this->data as $key=>$item){
                if (!empty($item['_destroy'])){
                    $this->{$model}->delete($item['id']);
                    unset($this->data[$key]);
                }
            }
            if (!empty($this->data)){
                $this->data = $this->{$model}->getRelated($this->data);
                if ($this->{$model}->saveAll($this->data)){
                    $this->status = 1;
                    $this->message = 'Records updated for model: ' . $model;
                }else{
                    $this->status = 0;
                    $this->message = 'Records could not be updated.  Failed validation';
                }
            }else{
                $this->status = 1;
                $this->message = 'Records deleted';
            }
        }else{
            $this->status = 0;
            $this->message = 'No data received';
        }
        $this->response = array(
            'status'=>$this->status,
            'message'=>$this->message,
            'data'=>$this->data
        );
    }

    public function runSQL(){
        if (!empty($_POST['sql'])){
            $sql = $_POST['sql'];
            $result = $this->{$this->modelName}->query($sql);
            $this->response = array(
                'status'=>1,
                'message'=>'Query successfully executed',
                'data'=>$result
            );
        }else{
            $this->response = array('status'=>0,'message'=>'Data missing','data'=>$_POST);
        }
    }
    
    public function output(){
        if (!empty($this->response['data'])){
            foreach($this->response['data'] as $key=>$item){
                if (is_array($item)){
                    foreach($item as $field=>$value){
                        if (is_numeric($value)){
                            $this->response['data'][$key][$field] = floatval($value);
                        }
                    }
                }
            }
        }
        echo json_encode($this->response);
    }
    
    public function render(){
        $this->output();
    }
}
?>
