<?php
/** 
 *  SvgLine.php
 *
 * @since 4.1.1
 */

class SvgLine extends SvgElement
{
    var $mX1;
    var $mY1;
    var $mX2;
    var $mY2;
    
    function SvgLine($x1=0, $y1=0, $x2=0, $y2=0, $style="", $transform="", $id="")
    {
        // Call the parent class constructor.
        $this->SvgElement();
        
        $this->mX1 = $x1;
        $this->mY1 = $y1;
        $this->mX2  = $x2;
        $this->mY2  = $y2;
        $this->mStyle = $style;
        $this->mTransform = $transform;
        $this->mId = $id;
        
    }
    
    function printElement()
    {
        print("<line x1=\"$this->mX1\" y1=\"$this->mY1\" x2=\"$this->mX2\" y2=\"$this->mY2\" ");
        
        if (is_array($this->mElements)) { // Print children, start and end tag.
            
            $this->printStyle();
            $this->printTransform();
            $this->printId();
            print(">\n");
            parent::printElement();
            print("</line>\n");
            
        } else { // Print short tag.
            
            $this->printStyle();
            $this->printTransform();
            $this->printId();
            print("/>\n");
            
        } // end else
    }
    
    function setShape($x1, $y1, $x2, $y2)
    {
        $this->mX1 = $x1;
        $this->mY1 = $y1;
        $this->mX2  = $x2;
        $this->mY2  = $y2;
    }
}
?>
