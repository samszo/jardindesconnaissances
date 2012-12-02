<?php
/** 
 *  SvgDesc.php
 *
 * @since 4.1.1
 */

class SvgDesc extends SvgElement
{
    var $mDesc;
    
    function SvgDesc($desc, $style="")
    {
        // Call the parent class constructor.
        $this->SvgElement();
        
        $this->mDesc = $desc;
        $this->mStyle = $style;
        
    }
    
    function printElement()
    {
        print("<desc ");
        $this->printStyle();
        print(">\n");
        print($this->mDesc."\n");
        parent::printElement();
        print("</desc>\n");
    }
    
}
?>
