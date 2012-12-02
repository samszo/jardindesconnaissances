<?php
/** 
 *  SvgElement.php
 *
 *  This is the base class for the different Svg Element Objects. Extend this
 *  class to create a new Svg Element.
 *
 * @since 4.1.1
 */

class SvgElement
{
    var $mElements = ""; // Initialize so warnings aren't issued when not used.
    var $mStyle;
    var $mTransform;
    var $mJs;
    var $mId;
    
    // The constructor.
    function SvgElement()
    {
        // Do nothing.
    }
    
    // Most Svg elements can contain child elements. This method calls the
    // printElement method of any child element added to this object by use
    // of the addChild method.
    function printElement()
    {
        // Loop and call
        if (is_array($this->mElements)) {
            foreach ($this->mElements as $child) {
            	//vérification lié à un problème de construction 
            	if($child){
	                $child->printElement();
            	}
            }
        }
    }
   
    // This method adds an object reference to the mElements array.
    function addChild(&$element)
    {
        $this->mElements[] =& $element;
    }
    
    // This method sends a message to the passed element requesting to be
    // added as a child.
    function addParent(&$parent)
    {
        if (is_subclass_of($parent, "SvgElement")) {
            $parent->addChild($this);
        }
    }
    
    // Most Svg elements have a style attribute.
    // It is up to the dervied class to call this method.
        function printStyle()
    {
        if ($this->mStyle != "") {
            print("style=\"$this->mStyle\" ");
        }
    }
    // This enables the style property to be set after initialization.
    function setStyle($string)
    {
        $this->mStyle = $string;
    }
    
    // Most Svg elements have a transform attribute.
    // It is up to the dervied class to call this method.
    function printTransform()
    {
        if ($this->mTransform != "") {
            print("transform=\"$this->mTransform\" ");
        }
    }
    
    // This enables the transform property to be set after initialization.
    function setTransform($string)
    {
        $this->mTransform = $string;
    }

    function printJs()
    {
        if ($this->mJs != "") {
            print($this->mJs." ");
        }
    }
    
    // This enables the transform property to be set after initialization.
    function setJs($string)
    {
        $this->mJs = $string;
    }
    
    function printId()
    {
        if ($this->mId != "") {
            print(" id=\"$this->mId\" ");
        }
    }
    
    // This enables the transform property to be set after initialization.
    function setId($string)
    {
        $this->mId = $string;
    }
    
    
    
    // Print out the object for debugging.
    function debug()
    {
        print("<pre>");
        print_r($this);
        print("</pre>");
    }

}
?>