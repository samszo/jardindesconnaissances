<?php
/* 
 *  This is the XXX Project Licence
 * do What The Fuck you want to Public License
 * 
 * Version 1.0, March 2000
 * Copyright (C) 2000 Banlu Kemiyatorn (]d).
 * 136 Nives 7 Jangwattana 14 Laksi Bangkok
 * Everyone is permitted to copy and distribute verbatim copies
 * of this license document, but changing it is not allowed.
 * 
 * Ok, the purpose of this license is simple
 * and you just
 * 
 * DO WHAT THE FUCK YOU WANT TO.
 * 
 */

/**
 * Ling_Function_ArrayMixer.php
 * 
 * @author Ling
 * 12 déc. 2009 16:52:36

 * @author samszo
 * modifier pour générer des séquences IEML : www.ieml.org
 * 14 11 2012 
 * 
 */

class ArrayMixer
{
    protected $result;
    protected $popNext;
    protected $matrix;
    protected $newArray;
    protected $oldArray;

    public function __construct($sep="_", $end="", $db=false)
    {
        $this->result  = array();
        $this->popNext = false;
        $this->matrix  = array();
        $this->newArray  = array();
        $this->oldArray  = array();
        $this->sep = $sep;
        $this->end = $end;
        $this->db = $db;
    }

    public function append(array $array)
    {
        $this->matrix[] = $array;
    }

    public function makeTree()
    {

        $lastArray = array_pop($this->matrix);
        if(count($this->matrix) > 0)
        {
            foreach(end($this->matrix) as $k => $v)
            {
                $this->newArray[$v] = $lastArray;
            }

            while(count($this->matrix) > 1)
            {
                $lastArray = array_pop($this->matrix);
                $this->oldArray = $this->newArray;
                $this->newArray = array();
                foreach(end($this->matrix) as $k => $v)
                {
                    $this->newArray[$v] = $this->oldArray;
                }
            }
        }
        else
        {
            $this->result = $lastArray;
        }

    }

    public function ListageArray($tb, $todisplay=array())
    {
        foreach($cit = new CachingIterator(new ArrayIterator($tb)) as $key => $value)
        {
            if(is_array($value))
            {
                if($this->popNext === true)
                {
                    array_pop($todisplay);
                    $this->popNext = false;
                }
                $todisplay[] = $key;
                self::ListageArray($value, $todisplay);
            }
            else
            {
                /**
					Ajout samszo pour :
					- passer en paramètre le séparateur de valeur et de fin
					- enregistrer dans une base de données
                 */
            	$prefix = implode($this->sep,$todisplay) . $this->sep;
            	$v = $prefix . $value . $this->sep . $this->end;
            	//supprime les séparateurs en trop
            	$v = str_replace($this->sep.$this->sep, $this->sep, $v);
                //enregistre la valeur
            	$this->result[] = $v;
                if($this->db)$this->db->ajouter(array("code"=>$v),false);
                
                if(!$cit->hasNext())
                {
                    $this->popNext = true;
                }
            }
        }
    }

    public function proceed()
    {
        $this->makeTree();
        $this->ListageArray($this->newArray);
    }

    public function result()
    {
        return $this->result;
    }

}