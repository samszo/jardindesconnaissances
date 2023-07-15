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

package com.degrafa{
	
	import com.degrafa.core.IGraphicsFill;
	import com.degrafa.core.IGraphicsStroke;
	import com.degrafa.core.collections.FillCollection;
	import com.degrafa.core.collections.GraphicsCollection;
	import com.degrafa.core.collections.StrokeCollection;
	
	import flash.display.DisplayObject;
	import flash.display.DisplayObjectContainer;
	import flash.display.Graphics;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.geom.Rectangle;
	
	import mx.events.FlexEvent;
	import mx.utils.NameUtil;


	import mx.core.IMXMLObject;
	import mx.events.PropertyChangeEvent;
	import mx.events.PropertyChangeEventKind;
	
	[Event(name="initialize", type="mx.events.FlexEvent")]
	[Event(name="propertyChange", type="mx.events.PropertyChangeEvent")]
	[Bindable(event="propertyChange",type="mx.events.PropertyChangeEvent")]		
	
	/**
	* Graphic is the base class for Degrafa objects that allow complete composition
	* GeometryGroup for example.
	* 
	* @see flash.display.Sprite
	**/
	public class Graphic extends Sprite implements IMXMLObject{	
			
		/**
		* Number that specifies the vertical position, in pixels, within the target.
		**/
		override public function get y():Number{
			return super.y;
		}
		override public function set y(value:Number):void{
			super.y = value;
		}
		
		/**
		* Number that specifies the horizontal position, in pixels, within the target.
		**/
		override public function get x():Number{
			return super.x;
		}
		override public function set x(value:Number):void{
			super.x = value;
		}
		
		private var _width:Number=0;
		[PercentProxy("percentWidth")]
		/**
		* Number that specifies the width, in pixels, in the target's coordinates.
		**/
		override public function get width():Number{
			return _width;
		}
		override public function set width(value:Number):void{
			_width = value;
			draw(null,null);
			dispatchEvent(new Event("change"));
		}
		
		
		
		private var _height:Number=0;
		[PercentProxy("percentHeight")]
		/**
		* Number that specifies the height, in pixels, in the target's coordinates.
		**/
		override public function get height():Number{
			return _height;
		}
		override public function set height(value:Number):void{
			_height = value;
		    draw(null,null);
		    dispatchEvent(new Event("change"));
		}
		
		/**
		* The default height, in pixels.
		**/
		public function get measuredHeight():Number{
			return _height;
		}

		/**
		* The default width, in pixels.
		**/
		public function get measuredWidth():Number{
			return _width;
		}
				
		private var _percentWidth:Number;
	    [Inspectable(environment="none")]
	     /**
	    * Number that specifies the width as a percentage of the target.
	    **/
	    public function get percentWidth():Number{
        	return _percentWidth;
    	}
	    public function set percentWidth(value:Number):void{
	        if (_percentWidth == value){return};
	        _percentWidth = value;
	        
    	}

    
	    private var _percentHeight:Number;
	    [Inspectable(environment="none")]
	    /**
	    * Number that specifies the height as a percentage of the target.
	    **/
	    public function get percentHeight():Number{
        	return _percentHeight;
    	}
	    public function set percentHeight(value:Number):void{
        	if (_percentHeight == value){return;}
			_percentHeight = value;
        	      	
	    }
		
		
		private var _target:DisplayObjectContainer;
		/**
		* A target DisplayObjectContainer that this graphic object should be added or drawn to.
		**/
		public function get target():DisplayObjectContainer{
			return _target;
		}
		public function set target(value:DisplayObjectContainer):void{
			
			if (!value){return;}
			
			//reparent if nessesary
			if (_target != value && _target!=null)
			{
				//remove this obejct from previous parent
				_target.removeChild(this);	
			}
			
			_target = value;
			_target.addChild(this);	
								
			//draw the obejct
			draw(null,null);
			endDraw(null);
						
			
		}
		
								
		private var _stroke:IGraphicsStroke;
		/**
		* Defines the stroke object that will be used for 
		* rendering this graphic object.
		**/
		public function get stroke():IGraphicsStroke{
			return _stroke;
		}
		public function set stroke(value:IGraphicsStroke):void{
			_stroke = value;
		}
		
		
		private var _fill:IGraphicsFill;
		/**
		* Defines the fill object that will be used for 
		* rendering this graphic object.
		**/
		public function get fill():IGraphicsFill{
			return _fill;
		}
		public function set fill(value:IGraphicsFill):void{
			_fill=value;
		}
		
		
		
		private var _fills:FillCollection;
		[Inspectable(category="General", arrayType="com.degrafa.core.IGraphicsFill")]
		[ArrayElementType("com.degrafa.core.IGraphicsFill")]
		/**
		* A array of IGraphicsFill objects.
		**/
		public function get fills():Array{
			if(!_fills){_fills = new FillCollection();}
			return _fills.items;
		}
		public function set fills(value:Array):void{			
			if(!_fills){_fills = new FillCollection();}
			_fills.items = value;
						
			//add a listener to the collection
			if(_fills && enableEvents){
				_fills.addEventListener(PropertyChangeEvent.PROPERTY_CHANGE,propertyChangeHandler);
			}
			
		}
		
		/**
		* Access to the Degrafa fill collection object for this graphic object.
		**/
		public function get fillCollection():FillCollection{
			if(!_fills){_fills = new FillCollection();}
			return _fills;
		}
		
		
		private var _strokes:StrokeCollection;
		[Inspectable(category="General", arrayType="com.degrafa.core.IGraphicsStroke")]
		[ArrayElementType("com.degrafa.core.IGraphicsStroke")]
		/**
		* A array of IStroke objects.
		**/
		public function get strokes():Array{
			if(!_strokes){_strokes = new StrokeCollection();}
			return _strokes.items;
		}
		public function set strokes(value:Array):void{	
			
			if(!_strokes){_strokes = new StrokeCollection();}
			_strokes.items = value;
			
			
			//add a listener to the collection
			if(_strokes && enableEvents){
				_strokes.addEventListener(PropertyChangeEvent.PROPERTY_CHANGE,propertyChangeHandler);
			}
		}
		
		/**
		* Access to the Degrafa stroke collection object for this graphic object.
		**/
		public function get strokeCollection():StrokeCollection{
			if(!_strokes){_strokes = new StrokeCollection();}
			return _strokes;
		}
				
		/**
		* Principle event handler for any property changes to a 
		* graphic object or it's child objects.
		**/
		private function propertyChangeHandler(event:PropertyChangeEvent):void{
			draw(null,null);
		}
				
		/**
		* Ends the draw phase for geometry objects.
		* 
		* @param graphics The current Graphics context being drawn to. 
		**/	
		public function endDraw(graphics:Graphics):void{
			
			if (fill){     
	        	fill.end(this.graphics);  
			}
		}
		
		
				
		/**
		* Begins the draw phase for geometry objects. All geometry objects 
		* override this to do their specific rendering.
		* 
		* @param graphics The current context to draw to.
		* @param rc A Rectangle object used for fill bounds. 
		**/
		public function draw(graphics:Graphics,rc:Rectangle):void{
						
			if (!parent){return;}
									
			if(percentWidth || percentHeight)
			{
				//calculate based on the parent
				_width = (parent.width/100)*_percentHeight;
				_height = (parent.height/100)*_percentHeight;
			}
			
			
			this.graphics.clear(); 
												
			if (stroke)
	        {
	        	if(!rc){
	        		stroke.apply(this.graphics,null);
	        	}
	        	else{
	        		stroke.apply(this.graphics,rc);	
	        	}
				
	        }
			else
			{
				this.graphics.lineStyle(0, 0xFFFFFF, 0);
			}
			
			 
	        if (fill){   
	        	
	        	if(!rc){
	        		var rect:Rectangle = new Rectangle(0,0,width,height);
	        		fill.begin(this.graphics, rect);
	        	}
	        	else{
	        		fill.begin(this.graphics, rc);
	        	}
	        	
	        }
	        	                
	        
		}
		
		
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
		
		private var _surpressEventProcessing:Boolean=false;
		/**
 		* Temporarily suppress event processing for this object.
 		**/
		public function get surpressEventProcessing():Boolean{
			return _surpressEventProcessing;
		}
		public function set surpressEventProcessing(value:Boolean):void{
			
			if(_surpressEventProcessing==true && value==false){
				_surpressEventProcessing=value;
				initChange("surpressEventProcessing",false,true,this);
			}
			else{
				_surpressEventProcessing=value;	
			}
		}
		
		/**
		* Dispatches an event into the event flow.
		*
		* @see EventDispatcher
		**/ 
		override public function dispatchEvent(event:Event):Boolean{
			if(_surpressEventProcessing){return false;}
			
			return(super.dispatchEvent(event));
			
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
		
		/**
		* Tests to see if a EventDispatcher instance has been created for this object.
		**/ 
		public function get hasEventManager():Boolean{
			return true;
		}
		
		//specific identity code
		
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
				name=_id;
				return _id;
			}
		}
		public function set id(value:String):void{
			_id = value;
			name=_id;
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
    	public function initialized(document:Object, id:String):void {
	    	
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
	        
	        //sprit has a name property and it is set 
	        //to the instance value. Make sure it is the 
	        //same as the id
	        name=_id;
	        
	        _document=document;
	        
	        if(enableEvents && !surpressEventProcessing){
	        	dispatchEvent(new FlexEvent(FlexEvent.INITIALIZE));
	        }
	        
        } 
			
	}
}