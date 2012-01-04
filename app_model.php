<?php
class AppModel extends Model {

    public function getRelated($data){
        foreach($data as $key=>$datum){
            if (is_array($datum)){
                foreach($datum as $k=>$value){
                    if (is_array($value)){
                        $relatedName = Inflector::classify($k);
                        print_r($relatedName);
                        $data[$key][$relatedName] = $value;
                        $this->bindModel(array('hasMany'=>array($relatedName)));
                    }else{
                        $data[$key][$this->alias][$k] = $value;
                    }
                    unset($data[$key][$k]);
                }
            }
        }
        return $data;
    }
}
?>
