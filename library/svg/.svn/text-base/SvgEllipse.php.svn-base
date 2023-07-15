<?php
/** 
 *  SvgEllipse.php
 *
 * @since 4.1.1
 */

class SvgEllipse extends SvgElement
{
    var $mCx;
    var $mCy;
    var $mRx;
    var $mRy;
    
    function SvgEllipse($cx=0, $cy=0, $rx=0, $ry=0, $style="", $transform="", $attrib="")
    {
        // Call the parent class constructor.
        $this->SvgElement();
        
        $this->mCx = $cx;
        $this->mCy = $cy;
        $this->mRx  = $rx;
        $this->mRy  = $ry;
        $this->mStyle = $style;
        $this->mTransform = $transform;
        $this->mAttrib = $attrib;       
    }
    
    function printElement()
    {
        print("<ellipse cx=\"$this->mCx\" cy=\"$this->mCy\" rx=\"$this->mRx\" ry=\"$this->mRy\" $this->mAttrib ");
        
        if (is_array($this->mElements)) { // Print children, start and end tag.
            
            $this->printStyle();
            $this->printTransform();
            print(">\n");
            parent::printElement();
            print("</ellipse>\n");
            
        } else { // Print short tag.
            
            $this->printStyle();
            $this->printTransform();
            print("/>\n");
            
        } // end else
        
    }
    
    function setShape($cx, $cy, $rx, $ry)
    {
        $this->mCx = $cx;
        $this->mCy = $cy;
        $this->mRx  = $rx;
        $this->mRy  = $ry;
    }
}
?>
