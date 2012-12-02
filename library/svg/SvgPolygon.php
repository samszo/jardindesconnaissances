<?php
/** 
 *  SvgPolygon.php
 *
 * @since 4.1.1
 */

class SvgPolygon extends SvgElement
{
    var $mPoints;
    
    function SvgPolygon($points=0, $style="", $transform="", $js="", $id="")
    {
        // Call the parent class constructor.
        $this->SvgElement();
        
        $this->mPoints = $points;
        $this->mStyle = $style;
        $this->mTransform = $transform;
        $this->mJs = $js;
        $this->mId = $id;
        
    }
    
    function printElement()
    {
        print("<polygon points=\"$this->mPoints\" ");
        
        if (is_array($this->mElements)) { // Print children, start and end tag.
            
            $this->printStyle();
            $this->printTransform();
            $this->printJs();
            $this->printId();
            print(">\n");
            parent::printElement();
            print("</polygon>\n");
            
        } else { // Print short tag.
            
            $this->printStyle();
            $this->printTransform();
            $this->printJs();
            $this->printId();
            print("/>\n");
            
        } // end else
        
    }
    
    function setShape($points)
    {
        $this->mPoints = $points;
    }
}
?>
