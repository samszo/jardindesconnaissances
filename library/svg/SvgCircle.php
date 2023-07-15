<?php
/** 
 *  SvgCircle.php
 *
 * @since 4.1.1
 */

class SvgCircle extends SvgElement
{
    var $mCx;
    var $mCy;
    var $mR;
    
    function SvgCircle($cx=0, $cy=0, $r=0, $style="", $transform="", $js="", $id="")
    {
        // Call the parent class constructor.
        $this->SvgElement();
        
        $this->mCx = $cx;
        $this->mCy = $cy;
        $this->mR  = $r;
        $this->mStyle = $style;
        $this->mTransform = $transform;
        $this->mJs = $js;
        $this->mId = $id;
        
    }
    
    function printElement()
    {
        print("<circle cx=\"$this->mCx\" cy=\"$this->mCy\" r=\"$this->mR\" ");
        
        if (is_array($this->mElements)) { // Print children, start and end tag.
            
            
            $this->printStyle();
            $this->printTransform();
            $this->printJs();
            $this->printId();
            print(">\n");
            parent::printElement();
            print("</circle>\n");
            
        } else { // Print short tag.
            
            $this->printStyle();
            $this->printTransform();
            $this->printJs();
            $this->printId();
            print("/>\n");
            
        } // end else
        
    } // end printElement
    
    function setShape($cx, $cy, $r)
    {
        $this->mCx = $cx;
        $this->mCy = $cy;
        $this->mR  = $r;
    }
}
?>
