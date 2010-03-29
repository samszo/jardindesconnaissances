///////////////////////////////////////
// COPYRIGHT ERNO AAPA - FINFLEX.FI
//
// Free for personal usage only
// To use in comercial contact me first
//
///////////////////////////////////////
package com.finflex.piemenu
{
    //events/myEvents/EnableChangeEventConst.as
    
    import flash.events.Event;

    public class PieMenuEvent extends Event
    {   
        // Public constructor. 
        public function PieMenuEvent(type:String) {
                // Call the constructor of the superclass.
                super(type);
                
        }

        // Define static constant.
        public static const ITEM_CLOSED:String = "itemClosed";
        public static const MENU_CLOSED:String = "menuClosed";
        
        // Override the inherited clone() method. 
        override public function clone():Event {
            return new PieMenuEvent(type);
        }
    }
}