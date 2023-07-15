////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2008 Jason Hawryluk, Juan Sanchez, Andy McIntosh, Ben Stucki 
// and Pavan Podila.
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
////////////////////////////////////////////////////////////////////////////////
package com.degrafa.core{
			
	import flash.events.Event;
	import flash.events.EventDispatcher;
	
	import mx.core.IMXMLObject;
	import mx.events.FlexEvent;
	import mx.events.PropertyChangeEvent;
	import mx.events.PropertyChangeEventKind;
	import mx.utils.NameUtil;
	
	[Event(name="initialize", type="mx.events.FlexEvent")]
	[Event(name="propertyChange", type="mx.events.PropertyChangeEvent")]
	
	/**
 	* Base class for all event enabled Degrafa objects.
 	**/ 
	public class DegrafaObject implements IDegrafaObject, IMXMLObject{
		
		//if false the internal listeners will not be 
		//set for the objects at creation time in other words 
		//if you don't want runtime events set this to false
		//if you do then set it to true
		
		private var _enableEvents:Boolean=true;
		/**
 		* Enable events for this object.
 		**/
		public function get enableEvents():Boolean{
			return _enableEvents;
		}
		public function set enableEvents(value:Boolean):void{
			_enableEvents=value;
		}
		
		//if true all event processing will stop being dispatched
		//used when you need to update many properties when set back 
		//to true the event that gets dispatched will cause the display
		//update (draw)
		private var _suppressEventProcessing:Boolean=false;
		/**
 		* Temporarily suppress event processing for this object.
 		**/
		public function get suppressEventProcessing():Boolean{
			return _suppressEventProcessing;
		}
		public function set suppressEventProcessing(value:Boolean):void{
			if(_suppressEventProcessing==true && value==false){
				_suppressEventProcessing=value;
				initChange("surpressEventProcessing",false,true,this);
			}
			else{
				_suppressEventProcessing=value;	
			}
		}
		
		/**
		* Tests to see if a EventDispatcher instance has been created for this object.
		**/  
		public function get hasEventManager():Boolean{
			return (_eventDispatcher) ?  true:false;
		}
		
		/**
		* Registers an event listener object with an EventDispatcher object so that the listener receives notification of an event.
		*
		* @see EventDispatcher
		**/ 		
		public function addEventListener(type:String, listener:Function, useCapture:Boolean = false, priority:int = 0, useWeakReference:Boolean = false):void{
	        eventDispatcher.addEventListener(type, listener, useCapture, priority);
	    }
	    
	    /**
		* Dispatches an event into the event flow.
		*
		* @see EventDispatcher
		**/ 
	    public function dispatchEvent(evt:Event):Boolean{
	    	if(_suppressEventProcessing){
	        	evt.stopImmediatePropagation();
	     		return false;
	     	}
	     	
	     	return eventDispatcher.dispatchEvent(evt);
	     	
	    }
	    
	    /**
		* Checks whether the EventDispatcher object has any listeners registered for a specific type of event.
		*
		* @see EventDispatcher
		**/ 
	    public function hasEventListener(type:String):Boolean{
	        return eventDispatcher.hasEventListener(type);
	    }
	    
	    /**
		* Removes a listener from the EventDispatcher object.
		*
		* @see EventDispatcher
		**/
	    public function removeEventListener(type:String, listener:Function, useCapture:Boolean = false):void{
	        eventDispatcher.removeEventListener(type, listener, useCapture);
	    }
	    
	    /**
		* Checks whether an event listener is registered with this EventDispatcher object or any of its ancestors for the specified event type.
		*
		* @see EventDispatcher
		**/
	    public function willTrigger(type:String):Boolean {
	        return eventDispatcher.willTrigger(type);
    	}
		
		/**
		* Dispatches an property change event into the event flow.
		**/
		public function dispatchPropertyChange(bubbles:Boolean = false, 
		property:Object = null, oldValue:Object = null, 
		newValue:Object = null, source:Object = null):Boolean{
			return dispatchEvent(new PropertyChangeEvent("propertyChange",bubbles,false,PropertyChangeEventKind.UPDATE,property,oldValue,newValue,source));
		}
		
		/**
		* Helper function for dispatching property changes
		**/
		public function initChange(property:String,oldValue:Object,newValue:Object,source:Object):void{
			if(hasEventManager){
				dispatchPropertyChange(false,property,oldValue,newValue,source);
			}
		}
		
		
		private var _eventDispatcher:EventDispatcher;		
		/**
		* EventDispatcher instance for this object.
		**/
		protected function get eventDispatcher():EventDispatcher{
			if(!_eventDispatcher){
				_eventDispatcher=new EventDispatcher(this)
			}
			
			return _eventDispatcher;
		}
		protected function set eventDispatcher(value:EventDispatcher):void{
			_eventDispatcher = value;
		}
		
		private var _id:String;
		/**
		* The identifier used by document to refer to this object.
		**/ 
		public function get id():String{
			
			if(_id){
				return _id;	
			}
			else{
				_id =NameUtil.createUniqueName(this);
				return _id;
			}
		}
		public function set id(value:String):void{
			_id = value;
		}
		
		/**
		* The name that refers to this object.
		**/ 
		public function get name():String{
			return id;
		}

		private var _document:Object;
		/**
		*  The MXML document that created this object.
		**/
		public function get document():Object{
			return _document;
		}
			
		/**
		* Called after the implementing object has been created and all component properties specified on the MXML tag have been initialized.
		* 
		* @param document The MXML document that created this object.
		* @param id The identifier used by document to refer to this object.  
		**/
    	public function initialized(document:Object, id:String):void{
	        
	        //if the id has not been set (through as perhaps)
	        if(!_id){	        
		        if(id){
		        	_id = id;
		        }
		        else{
		        	//if no id specified create one
		        	_id = NameUtil.createUniqueName(this);
		        }
	        }
	        
	        _document=document;
	        	        
	        if(enableEvents && ! _suppressEventProcessing){
	        	dispatchEvent(new FlexEvent(FlexEvent.INITIALIZE));
	        }
	        	        
	        	        
        } 
		
	}
}