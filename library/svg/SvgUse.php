<?php
/** 
 *  SvgUse.php
 *
 */

class SvgUse extends SvgElement
{
    var $mHref;
	
    function SvgUse($x=0, $y=0, $transform="", $href="", $id="", $js="")
    {
        // Call the parent class constructor.
        $this->SvgElement();
        
        $this->mX = $x;
        $this->mY = $y;
        $this->mTransform = $transform;
        $this->mHref = $href;
        $this->mId = $id;
        $this->mJs = $js;
    }
    
    function printElement()
    {
        print("<use ");
        
        if ($this->mX != "") {
            print("x=\"$this->mX\" ");
        }
        if ($this->mY != "") {
            print("y=\"$this->mY\" ");
        }
        $this->printTransform();
        if ($this->mHref != "") {
            print("xlink:href=\"$this->mHref\" ");
        }
        $this->printId();
        $this->printJs();
        print("/>\n");

    }
    
}
?>