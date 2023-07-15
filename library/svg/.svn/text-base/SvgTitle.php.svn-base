<?php
/** 
 *  SvgTitle.php
 *
 * @since 4.1.1
 */

class SvgTitle extends SvgElement
{
    var $mTitle;
    
    function SvgTitle($title, $style="")
    {
        // Call the parent class constructor.
        $this->SvgElement();
        
        $this->mTitle = $title;
        $this->mStyle = $style;
        
    }
    
    function printElement()
    {
        print("<title ");
        $this->printStyle();
        print(">\n");
        print($this->mTitle."\n");
        parent::printElement();
        print("</title>\n");
    }
    
}
?>
