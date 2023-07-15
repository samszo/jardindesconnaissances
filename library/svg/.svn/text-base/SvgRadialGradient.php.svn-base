<?php
/** 
 *  SvgRadialGradient.php
 *
 *  class pour cr�er des d�grad�s lin�aires
 *
 * lucky semiosis 29/08/04
 */

class SvgRadialGradient extends SvgElement
{
    var $mId;
    var $mOffset;
    var $mColor;

    function SvgRadialGradient($id="", $offset, $color)
    {
        // Call the parent class constructor.
        $this->SvgElement();
        
        $this->mId = $id;
        $this->mOffset = $offset;
        $this->mColor  = $color;
       
    }
    
    function printElement()
    {
        print("<radialGradient id=\"$this->mId\" spreadMethod=\"pad\" gradientUnits=\"objectBoundingBox\" cx=\"50%\" cy=\"50%\" r=\"50%\" fx=\"50%\" fy=\"50%\" >\n");
        if (is_array($this->mColor)) { // Print children, start and end tag.
            $ct = count($this->mColor)-1;
			for ($i = 0; $i <= $ct; $i++) {
				$os = $this->mOffset[$i];
				$co = $this->mColor[$i];
           		print("<stop offset=\"" .$os ."\" stop-color=\"" .$co ."\" />\n");
			}
            print("\n");
	        parent::printElement();
            print("</radialGradient>\n");
            
        } else { // Print short tag.
			print("<stop offset=\"$this->mOffset%\" stop-color=\"$this->mColor\" />\n");
            print(">\n");           
	        parent::printElement();
            print("</radialGradient>\n");
            
        } // end else
        
    }
    function setShape($id, $offset, $color)
    {
        $this->mId = $id;
        $this->mOffset = $offset;
        $this->mColor  = $color;
    }
   
}
?>
