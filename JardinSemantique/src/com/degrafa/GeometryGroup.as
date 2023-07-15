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
	import com.degrafa.core.collections.GeometryCollection;
	
	import flash.display.DisplayObject;
	import flash.display.Graphics;
	import flash.geom.Rectangle;
	import mx.events.PropertyChangeEvent;
	
	[DefaultProperty("geometry")]		
	[Bindable(event="propertyChange")]	
	/**
	* GeometryGroup is where composition is achieved for 
	* all Degrafa objects that render to a graphics context. Nested GeometryGroups
	* can be added each of which may contain IGraphic or IGeometry type objects. 
	* GeometryGroup is the principle building block for compositing.
	**/
	public class GeometryGroup extends Graphic implements IGraphic, IGeometry{		
	
		public function GeometryGroup(){
			super();
		} 						
		
		private var _geometry:GeometryCollection;
		[Inspectable(category="General", arrayType="com.degrafa.IGeometry")]
		[ArrayElementType("com.degrafa.IGeometry")]
		/**
		* A array of IGeometry objects. Objects of type GraphicText, GraphicImage
		* and GeometryGroup are added to the target display list.	
		**/
		public function get geometry():Array{
			if(!_geometry){_geometry = new GeometryCollection();}
			return _geometry.items;
		}
		public function set geometry(value:Array):void
		{
			if(!_geometry){_geometry = new GeometryCollection();}
			_geometry.items = value;
			
			
			//add the children is required
			for each (var item:IGeometry in _geometry.items){
				if(item is IGraphic){
					addChild(DisplayObject(item));
				}
			}
						
			//add a listener to the collection
			if(_geometry && enableEvents){
				_geometry.addEventListener(PropertyChangeEvent.PROPERTY_CHANGE,propertyChangeHandler);
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
			draw(null,null);
		}
		
		/**
		* Begins the draw phase for graphic objects. All graphic objects 
		* override this to do their specific rendering.
		* 
		* @param graphics The current context to draw to.
		* @param rc A Rectangle object used for fill bounds. 
		**/							
		override public function draw(graphics:Graphics,rc:Rectangle):void{			
			if(!parent){return;}
									
			super.draw(null,null);
			
			if (_geometry){
				for each (var geometryItem:IGeometry in _geometry.items){
					
					if(geometryItem is IGraphic){
						//a IGraphic is a sprite and does not draw to 
						//this graphics object
						geometryItem.draw(null,null);
					}
					else{
						geometryItem.draw(this.graphics,null);
					}
				}
			}
						
			super.endDraw(null);
	        
	    }
		
		/**
		* Data is required for the IGeometry interface and has no effect here.
		* @private  
		**/		
		public function get data():String{return "";}
		public function set data(value:String):void{}
		
		
	}
}