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
package com.degrafa.skins
{
	import com.degrafa.core.collections.FillCollection;
	import com.degrafa.core.collections.GeometryCollection;
	import com.degrafa.core.collections.StrokeCollection;
	import com.degrafa.core.IGraphicSkin;
	import com.degrafa.core.IGraphicsFill;
	import com.degrafa.core.IGraphicsStroke;
	import com.degrafa.core.IDegrafaObject;
	
	import flash.display.DisplayObjectContainer;
	import flash.display.Graphics;
	import flash.events.Event;
	import flash.geom.Rectangle;
	
	import mx.events.PropertyChangeEvent;
	import mx.events.PropertyChangeEventKind;
	import mx.skins.Border;
	
	
	
	[Exclude(name="graphicsData", kind="property")]		
	[Exclude(name="percentWidth", kind="property")]	
	[Exclude(name="percentHeight", kind="property")]	
	[Exclude(name="target", kind="property")]	
	
	[DefaultProperty("geometry")]
	
	[Bindable(event="propertyChange")] 		
	
	/**
	* GraphicBorderSkin is an extension of Border for use declarativly.
	**/
	public class GraphicBorderSkin extends Border implements IGraphicSkin{
		
		public function GraphicBorderSkin(){
			super();
		}
						
		
		private var _stroke:IGraphicsStroke;
		/**
		* Defines the stroke object that will be used for 
		* rendering this geometry object.
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
		* rendering this geometry object.
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
		* An array of subsequent IGeometry items added as children
		* to this shape.
		**/
		private var _geometry:GeometryCollection;
		[Inspectable(category="General", arrayType="com.degrafa.IGeometry")]
		[ArrayElementType("com.degrafa.IGeometry")]
		/**
		* A array of IGeometry objects. This will not accept IGraphic objects.
		**/
		public function get geometry():Array{
			if(!_geometry){_geometry = new GeometryCollection();}
			return _geometry.items;;
		}
		public function set geometry(value:Array):void{
			
			if(!_geometry){_geometry = new GeometryCollection();}
			_geometry.items = value;
			
			for each (var geometryItem:IDegrafaObject in _geometry.items){
				//add listener if events are enabled
				if(geometryItem.enableEvents){
					geometryItem.addEventListener(
					PropertyChangeEvent.PROPERTY_CHANGE,propertyChangeHandler,false,0,true);
				}
			}
		}
		
		
		/**
		* Access to the Degrafa geometry collection object for this graphic object.
		**/
		public function get geometryCollection():GeometryCollection{
			if(!_geometry){_geometry = new GeometryCollection();}
			return _geometry;
		}
		

		/**
		* Principle event handler for any property changes to a 
		* graphic object or it's child objects.
		**/
		private function propertyChangeHandler(event:PropertyChangeEvent):void{
			dispatchEvent(event);
			invalidateDisplayList();
			
		}

		
		//not required here but need for interface
		/**
		* @private
		**/
		public function get percentWidth():Number{return 0;}
	    public function set percentWidth(value:Number):void{}
	   	
	   	/**
		* @private
		**/
	    public function get percentHeight():Number{return 0;}
	    public function set percentHeight(value:Number):void{}	    			

		/**
		* @private
		**/
		public function get target():DisplayObjectContainer{return null;}
		public function set target(value:DisplayObjectContainer):void{}
		
		/**
		* @private
		**/
		public function set graphicsData(value:Array):void{}
		public function get graphicsData():Array{return null;}
		
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
		* Begins the draw phase for graphic objects. All graphic objects 
		* override this to do their specific rendering.
		* 
		* @param graphics The current context to draw to.
		* @param rc A Rectangle object used for fill bounds. 
		**/	
		public function draw(graphics:Graphics,rc:Rectangle):void
		{
			if(!parent){return;}
			
			this.graphics.clear();
									
			var i:int;
			
			if (geometry){
				for (i =0;i<geometry.length;i++)
				{
					geometry[i].draw(this.graphics,null);
				}			
			}
					
	    }
	    
	    /**
		* Draws the object and/or sizes and positions its children.
		**/
	    override protected function updateDisplayList(unscaledWidth:Number, unscaledHeight:Number):void{	    	
	       	draw(null,null);
	    	endDraw(null);	
	    }
	    	    
	    //event related stuff
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
		
	}
}