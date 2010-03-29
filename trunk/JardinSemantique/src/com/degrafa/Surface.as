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
	
	import com.degrafa.core.collections.FillCollection;
	import com.degrafa.core.collections.GraphicsCollection;
	import com.degrafa.core.collections.StrokeCollection;
	
	import flash.display.DisplayObject;
	import flash.events.Event;
	
	import mx.core.UIComponent;
	import mx.events.PropertyChangeEvent;
	import mx.events.PropertyChangeEventKind;

	[DefaultProperty("graphicsData")]
	[Bindable(event="propertyChange")]
	
	/**
	* Surface is a simple UIComponent extension that allows Degrafa objects 
	* to be added to it's display list. Fills and strokes set here have no 
	* effect and only serve organizational purposes.
	**/
	public class Surface extends UIComponent{
		
		public function Surface(){
			super();
		}
				
		
		private var _graphicsData:GraphicsCollection;
		[Inspectable(category="General", arrayType="com.degrafa.IGraphic")]
		[ArrayElementType("com.degrafa.IGraphic")]
		/**
		* A array of IGraphic objects. Objects of type GraphicText, GraphicImage
		* and GeometryGroup are accepted and to this objects display list.	
		**/
		public function get graphicsData():Array{
			if(!_graphicsData){_graphicsData = new GraphicsCollection();}
			return _graphicsData.items;
		}
		public function set graphicsData(value:Array):void{
			
			if(!_graphicsData){_graphicsData = new GraphicsCollection();}
			
			_graphicsData.items = value;
			
			
			for each(var item:IGraphic in _graphicsData.items){
						
				addChild(DisplayObject(item));
				
				if (item.target==null){
					item.target=this;
				}
			}
						
			if(_graphicsData && enableEvents){
				_graphicsData.addEventListener(PropertyChangeEvent.PROPERTY_CHANGE,propertyChangeHandler);
			}
			
				
		}
				
		/**
		* Access to the Degrafa graphics collection object for this graphic object.
		**/
		public function get graphicsCollection():GraphicsCollection{
			if(!_graphicsData){_graphicsData = new GraphicsCollection();}
			return _graphicsData;
		}
				
		/**
		* Principle event handler for any property changes to a 
		* graphic object or it's child objects.
		**/
		private function propertyChangeHandler(event:PropertyChangeEvent):void{
			dispatchEvent(event);
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
		* Draws the object and/or sizes and positions its children.
		**/
		protected override function updateDisplayList(unscaledWidth:Number, unscaledHeight:Number):void{
			super.updateDisplayList(unscaledWidth, unscaledHeight);
			
			setActualSize(getExplicitOrMeasuredWidth(), getExplicitOrMeasuredHeight());
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