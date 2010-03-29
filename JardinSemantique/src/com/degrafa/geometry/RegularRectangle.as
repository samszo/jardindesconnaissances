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
package com.degrafa.geometry{
	
	import com.degrafa.IGeometry;
	import flash.geom.Rectangle;
	import flash.display.Graphics;
	import flash.display.DisplayObject;
	
	[Bindable]		
	/**
 	*  The RegularRectangle element draws a regular rectangle using the specified x,y,
 	*  width and height.
 	*  
 	*  @see http://samples.degrafa.com/RegularRectangle/RegularRectangle.html
 	*  
 	**/	
	public class RegularRectangle extends Geometry implements IGeometry{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The regular rectangle constructor accepts 4 optional arguments that define it's 
	 	* x, y, width and height.</p>
	 	* 
	 	* @param x A number indicating the upper left x-axis coordinate.
	 	* @param y A number indicating the upper left y-axis coordinate.
	 	* @param width A number indicating the width.
	 	* @param height A number indicating the height. 
	 	*/		
		public function RegularRectangle(x:Number=0,y:Number=0,width:Number=0,height:Number=0){
			super();
			
			this.x=x;
			this.y=y;
			this.width=width;
			this.height=height;
			
		}
		
		/**
		* RegularRectangle short hand data value.
		* 
		* <p>The regular rectangle data property expects exactly 4 values x, 
		* y, width and height separated by spaces.</p>
		* 
		* @see Geometry#data
		* 
		**/
		override public function set data(value:String):void{	
			
			if(super.data != value){
				super.data = value;
			
				//parse the string on the space
				var tempArray:Array = value.split(" ");
				
				if (tempArray.length == 4){
					_x=tempArray[0];
					_y=tempArray[1];
					_width=tempArray[2];
					_height=tempArray[3];
				}	
				
				invalidated = true;
				
			}
		} 
		
		private var _x:Number=0;
		/**
		* The x-axis coordinate of the upper left point of the regular rectangle. If not specified 
		* a default value of 0 is used.
		**/
		public function get x():Number{
			return _x;
		}
		public function set x(value:Number):void{
			if(_x != value){
				_x = value;
				invalidated = true;
			}
		}
		
		
		private var _y:Number=0;
		/**
		* The y-axis coordinate of the upper left point of the regular rectangle. If not specified 
		* a default value of 0 is used.
		**/
		public function get y():Number{
			return _y;
		}
		public function set y(value:Number):void{
			if(_y != value){
				_y = value;
				invalidated = true;
			}
		}
		
		private var _width:Number=0;
		/**
		* The width of the regular rectangle.
		**/
		public function get width():Number{
			return _width;
		}
		public function set width(value:Number):void{
			if(_width != value){
				_width = value;
				invalidated = true;
			}
		}
		
		
		private var _height:Number=0;
		/**
		* The height of the regular rectangle.
		**/
		public function get height():Number{
			return _height;
		}
		public function set height(value:Number):void{
			if(_height != value){
				_height = value;
				invalidated = true;
			}
		}
		
		
		
		private var _bounds:Rectangle;
		/**
		* The tight bounds of this element as represented by a Rectangle object. 
		**/
		public function get bounds():Rectangle{
			return _bounds;	
		}
		
		/**
		* Calculates the bounds for this element. 
		**/
		private function calcBounds():void{
			_bounds = new Rectangle(x,y,width,height);
			
		}	
		
		/**
		* @inheritDoc 
		**/
		override public function preDraw():void{
			if(invalidated){
			
				commandStack = [];
				
				commandStack.push({type:"rectangle", x:x,
					y:y,width:width,height:height});	
				
			
				calcBounds();
				invalidated = false;
			}
			
		}
		
		/**
		* An Array of flash rendering commands that make up this element. 
		**/
		protected var commandStack:Array=[];				
		
		/**
		* Begins the draw phase for geometry objects. All geometry objects 
		* override this to do their specific rendering.
		* 
		* @param graphics The current context to draw to.
		* @param rc A Rectangle object used for fill bounds. 
		**/	
		override public function draw(graphics:Graphics,rc:Rectangle):void
		{	
			//re init if required
		 	preDraw();
		 							
			//apply the fill retangle for the draw
			if(!rc){				
				super.draw(graphics,_bounds);	
			}
			else{
				super.draw(graphics,rc);
			}
			
			var item:Object;
						
			//draw each item in the array
			for each (item in commandStack){
        		
        		graphics.drawRect(item.x,item.y,
        		item.width,item.height);
        		
        	}
				 	 		 	 	
	 	 	super.endDraw(graphics);	
	 	 	
	  		
		}
	}
}