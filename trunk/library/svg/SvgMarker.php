<?php
/** 
 *  SvgMarker.php
 *
 * @since 4.1.1
 */

class SvgMarker extends SvgElement
{
    var $mId;
    var $mRefX;
    var $mRefY;
    var $mMarkerUnits;
    var $mMarkerWidth;
    var $mMarkerHeight;
    var $mOrient;
    
    function SvgMarker($id, $refX="", $refY="", $markerUnits="", $markerWidth="", $markerHeight="", $orient="")
    {
        // Call the parent class constructor.
        $this->SvgElement();
        
        $this->mId = $id;
        $this->mRefX = $refX;
        $this->mRefY  = $refY;
        $this->mMarkerUnits = $markerUnits;
        $this->mMarkerWidth = $markerWidth;
        $this->mMarkerHeight = $markerHeight;
        $this->mOrient = $orient;
        
    }
    
    function printElement()
    {
        print("<marker id=\"$this->mId\" ");
        
        // Print the attributes only if they are defined.
        if ($this->mRefX != "")          { print ("refX=\"$this->mRefX\" "); }
        if ($this->mRefY != "")          { print ("refY=\"$this->mRefY\" "); }
        if ($this->mMarkerUnits != "")   { print ("markerUnits=\"$this->mMarkerUnits\" "); }
        if ($this->mMarkerWidth != "")   { print ("markerWidth=\"$this->mMarkerWidth\" "); }
        if ($this->mMarkerHeight != "")  { print ("markerHeight=\"$this->mMarkerHeight\" "); }
        if ($this->mOrient != "")        { print ("orient=\"$this->mOrient\" "); }
        
        if (is_array($this->mElements)) { // Print children, start and end tag.
            
            print(">\n");
            parent::printElement();
            print("</marker>\n");
            
        } else {
            print("/>\n");
        } // end else
        
    } // end printElement
    
    function setShape($id, $refX="", $refY="", $markerUnits="", $markerWidth="", $markerHeight="", $orient="")
    {
        $this->mId = $id;
        $this->mRefX = $refX;
        $this->mRefY  = $refY;
        $this->mMarkerUnits = $markerUnits;
        $this->mMarkerWidth = $markerWidth;
        $this->mMarkerHeight = $markerHeight;
        $this->mOrient = $orient;
    }
}
?>
