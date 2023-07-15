<?php
/** 
 *  SvgAnimateTransform.php
 *
 * @since 4.1.1
 * by lucky semiosis
 */

class SvgAnimateTransform extends SvgElement
{
    var $mAttributeName;
    var $mAttributeType;
    var $mRepeatCount;
    var $mFrom;
    var $mTo;
    var $mBegin;
    var $mDur;
    var $mFill;
 	var $mType;
 	var $mAdditive;
     
    function SvgAnimateTransform($attributeName, $repeatCount="", $attributeType="", $from="", $to="", $begin="", $dur="", $fill="",$type="",$additive="")
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
		$this->mType = $type;
		$this->mAdditive = $additive;
       
    }
    
    function printElement()
    {
        print("<animateTransform attributeName=\"$this->mAttributeName\" ");
				       
        // Print the attributes only if they are defined.
        if ($this->mAttributeType != "") { print ("attributeType=\"$this->mAttributeType\" "); }
        if ($this->mRepeatCount != "")  { print ("repeatCount=\"$this->mRepeatCount\" "); }
	    if ($this->mFrom != "")  { print ("from=\"$this->mFrom\" "); }
       if ($this->mType != "")  { print ("type=\"$this->mType\" "); }
       if ($this->mAdditive != "")  { print ("additive=\"$this->mAdditive\" "); }
        if ($this->mTo != "")    { print ("to=\"$this->mTo\" "); }
        if ($this->mBegin != "") { print ("begin=\"$this->mBegin\" "); }
        if ($this->mDur != "")   { print ("dur=\"$this->mDur\" "); }
        if ($this->mFill != "")  { print ("fill=\"$this->mFill\" "); }
        
        if (is_array($this->mElements)) { // Print children, start and end tag.
            
            print(">\n");
            parent::printElement();
            print("</animateTransform>\n");
            
        } else {
            print("/>\n");
        } // end else
        
    } // end printElement
    
    function setShape($attributeName, $repeatCount, $attributeType="", $from="", $to="", $begin="", $dur="", $fill="",$type="",$additive="")
    {
        $this->mAttributeName = $attributeName;
        $this->mAttributeType = $attributeType;
         $this->mRepeatCount = $repeatCount;
       $this->mFrom  = $from;
        $this->mTo = $to;
        $this->mBegin = $begin;
        $this->mDur = $dur;
        $this->mFill = $fill;
 		$this->mType = $type;
		$this->mAdditive = $additive;
   }
}
?>
