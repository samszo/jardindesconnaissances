<?php
/** 
 *  SvgAnimateColor.php
 *
 * @since 4.1.1
 * by lucky semiosis
 */

class SvgAnimateColor extends SvgElement
{
    var $mAttributeName;
    var $mAttributeType;
    var $mRepeatCount;
    var $mFrom;
    var $mTo;
    var $mBegin;
    var $mDur;
    var $mFill;
    
    function SvgAnimateColor($attributeName, $repeatCount="", $attributeType="", $from="", $to="", $begin="", $dur="", $fill="")
    {
        // Call the parent class constructor.
        $this->SvgElement();
        
        $this->mAttributeName = $attributeName;
        $this->mAttributeType = $attributeType;
        $this->mRepeatCount = $repeatCount;
        $this->mFrom  = $from;
        $this->mTo = $to;
        $this->mBegin = $begin;
        $this->mDur = $dur;
        $this->mFill = $fill;
        
    }
    
    function printElement()
    {
        print("<animateColor attributeName=\"$this->mAttributeName\" ");
        
        // Print the attributes only if they are defined.
        if ($this->mAttributeType != "") { print ("attributeType=\"$this->mAttributeType\" "); }
         if ($this->mRepeatCount != "")  { print ("repeatCount=\"$this->mRepeatCount\" "); }
       if ($this->mFrom != "")  { print ("from=\"$this->mFrom\" "); }
        if ($this->mTo != "")    { print ("to=\"$this->mTo\" "); }
        if ($this->mBegin != "") { print ("begin=\"$this->mBegin\" "); }
        if ($this->mDur != "")   { print ("dur=\"$this->mDur\" "); }
        if ($this->mFill != "")  { print ("fill=\"$this->mFill\" "); }
        
        if (is_array($this->mElements)) { // Print children, start and end tag.
            
            print(">\n");
            parent::printElement();
            print("</animateColor>\n");
            
        } else {
            print("/>\n");
        } // end else
        
    } // end printElement
    
    function setShape($attributeName, $repeatCount, $attributeType="", $from="", $to="", $begin="", $dur="", $fill="")
    {
        $this->mAttributeName = $attributeName;
        $this->mAttributeType = $attributeType;
         $this->mRepeatCount = $repeatCount;
       $this->mFrom  = $from;
        $this->mTo = $to;
        $this->mBegin = $begin;
        $this->mDur = $dur;
        $this->mFill = $fill;
    }
}
?>
