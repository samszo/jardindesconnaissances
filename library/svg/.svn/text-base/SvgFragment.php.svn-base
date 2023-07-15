<?php
/** 
 *  SvgFragment.php
 *
 * @since 4.1.1
 */

class SvgFragment extends SvgElement
{
    var $mWidth;
    var $mHeight;
    var $mX;
    var $mY;
	var $mViewBox;
	var $mPreserveAspectRatio;
	
    function SvgFragment($width="100%", $height="100%", $x=0, $y=0, $style="", $viewBox="", $preserveAspectRatio="", $id="", $js="")
    {
        // Call the parent class constructor.
        $this->SvgElement();
        
        $this->mWidth = $width;
        $this->mHeight = $height;
        $this->mStyle = $style;
        $this->mX = $x;
        $this->mY = $y;
        $this->mViewBox = $viewBox;
        $this->mPreserveAspectRatio = $preserveAspectRatio;
        $this->mId = $id;
        $this->mJs = $js;
    }
    
    function printElement()
    {
        print("<svg width=\"$this->mWidth\" height=\"$this->mHeight\" ");
        
        if ($this->mX != "") {
            print("x=\"$this->mX\" ");
        }
        if ($this->mY != "") {
            print("y=\"$this->mY\" ");
        }
        if ($this->mPreserveAspectRatio != "") {
            print("preserveAspectRatio=\"$this->mPreserveAspectRatio\" ");
        }
        if ($this->mViewBox != "") {
            print("viewBox=\"$this->mViewBox\" ");
        }
        if ($this->mId != "") {
            print("id=\"$this->mId\" ");
        }
        $this->printJs();
        
        print('xmlns="http://www.w3.org/2000/svg" ');
        print('xmlns:xlink="http://www.w3.org/1999/xlink" ');
        $this->printStyle();
        print(">\n");

        parent::printElement();
        print("</svg>\n");
    }
    
    function bufferObject()
    {
        ob_start();
        $this->printElement();
        $buff = ob_get_contents();
	    ob_end_clean();
        return $buff;
    }
}
?>