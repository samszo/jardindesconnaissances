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
	
	import com.degrafa.core.IGraphicsFill;
	import com.degrafa.IGeometry;
	import flash.geom.Rectangle;
	import flash.display.Graphics;
	import com.degrafa.core.IGraphicsFill;
	import flash.display.DisplayObject;
			
	[Exclude(name="fill", kind="property")]
	[Bindable]	
	/**
 	*  The HorizontalLine element draws a horizontal line using the specified x, y, 
 	 * and x1 coordinate values.
 	*  
 	*  @see http://samples.degrafa.com/HorizontalLine/HorizontalLine.html	    
 	* 
 	**/
	public class HorizontalLine extends Geometry implements IGeometry{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The horizontal line constructor accepts 3 optional arguments that define it's 
	 	* center point and radius.</p>
	 	* 
	 	* @param x A number indicating the starting x-axis coordinate.
	 	* @param y A number indicating the starting y-axis coordinate.
	 	* @param x1 A number indicating the ending x-axis coordinate.
	 	*/		 
		public function HorizontalLine(x:Number=0,y:Number=0,x1:Number=0){
			super();
			
			this.x=x;
			this.y=y;
			this.x1=x1;
		}
		
		/**
		* HorizontalLine short hand data value.
		* 
		* <p>The horizontal line data property expects exactly 3 values x, 
		* y and x1 separated by spaces.</p>
		* 
		* @see Geometry#data
		* 
		**/
		override public function set data(value:String):void{
			if(super.data != value){
				super.data = value;
			
				//parse the string on the space
				var tempArray:Array = value.split(" ");
				
				if (tempArray.length == 3){
					_x=tempArray[0];
					_y=tempArray[1];
					_x1=tempArray[2];
				}
				
				invalidated = true;
			}
			
		}  
		
		
		private var _x:Number=0;
		/**
		* The x-coordinate of the start point of the line. If not specified 
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
		* The y-coordinate of the start point of the line. If not specified 
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
				
						
		private var _x1:Number=0;
		/**
		* The x-coordinate of the end point of the line. If not specified 
		* a default value of 0 is used.
		**/
		public function get x1():Number{
			return _x1;
		}
		public function set x1(value:Number):void{
			if(_x1 != value){
				_x1 = value;
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
			_bounds = new Rectangle(Math.min(x,x1),y,x1-x,1);
		}	
		
		/**
		* @inheritDoc 
		**/
		override public function preDraw():void{
			if(invalidated){
			
				commandStack = [];
				
				commandStack.push({type:"m",x:x,y:y});	
				commandStack.push({type:"l",x:x1,y:y});
			
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
		override public function draw(graphics:Graphics,rc:Rectangle):void{			
			
			
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
        		if(item.type=="m"){
        			graphics.moveTo(item.x,item.y);
        		}
        		else{
        			graphics.lineTo(item.x,item.y);
        		}
        	}
				 	 		 	 	
	 	 	super.endDraw(graphics);
			        
	        
		}
	}
}