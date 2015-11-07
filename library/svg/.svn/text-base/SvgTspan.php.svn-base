<?php
/** 
 *  SvgTspan.php
 *
 * @since 4.1.1
 */

class SvgTspan extends SvgElement
{
    var $mX;
    var $mY;
    var $mText;
    
    function SvgTspan($x=0, $y=0, $text="", $style="", $transform="")
    {
        // Call the parent class constructor.
        $this->SvgElement();
        
        $this->mX = $x;
        $this->mY = $y;
        $this->mText  = $text;
        $this->mStyle = $style;
        $this->mTransform = $transform;
        
    }
    
    function printElement()
    {
        print("<tspan x=\"$this->mX\" y=\"$this->mY\" ");
        
        if (is_array($this->mElements)) { // Print children, start and end tag.
            
            $this->printStyle();
            $this->printTransform();
            print(">\n");
            print($this->mText);
            parent::printElement();
            print("</tspan>\n");
            
        } else { // Print short tag.
            
            $this->printStyle();
            $this->printTransform();
            print(">\n");
            print($this->mText);
            print("\n</tspan>\n");
            
        } // end else
        
    }
    
    function setShape($x, $y, $text)
    {
        $this->mX = $x;
        $this->mY = $y;
        $this->mText  = $text;
    }
}
?>
