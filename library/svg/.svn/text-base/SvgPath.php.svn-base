<?php
/** 
 *  SvgPath.php
 *
 * @since 4.1.1
 */

class SvgPath extends SvgElement
{
    var $mD;
    
    function SvgPath($d="", $style="", $transform="", $attrib="", $id="")
    {
        // Call the parent class constructor.
        $this->SvgElement();
        
        $this->mD = $d;
        $this->mStyle = $style;
        $this->mTransform = $transform;
        $this->mAttrib = $attrib;       
        $this->mId = $id;       
        
    }
    
    function printElement()
    {
        print("<path d=\"$this->mD\" $this->mAttrib");
        
        if (is_array($this->mElements)) { // Print children, start and end tag.
            
            $this->printStyle();
            $this->printTransform();
            $this->printId();
            print(">\n");
            parent::printElement();
            print("</path>\n");
            
        } else { // Print short tag.
            
            $this->printStyle();
            $this->printTransform();
            $this->printId();
            print("/>\n");
            
        } // end else
    }
    
    function setShape($d)
    {
        $this->mD = $d;
    }
}
?>
