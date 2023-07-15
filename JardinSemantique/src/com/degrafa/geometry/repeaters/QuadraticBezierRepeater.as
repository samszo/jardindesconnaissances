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
package com.degrafa.geometry.repeaters{
	
	import com.degrafa.geometry.QuadraticBezier;
	
	import flash.display.Graphics;
	import flash.geom.Rectangle;

	[Bindable]
	/**
 	*  The QuadraticBezierRepeater element draws a quadratic Bézier using the specified 
 	*  start point, end point and control point. Then progressively repeats the curve 
 	*  using count and offsets.
 	*  
 	*  @see http://samples.degrafa.com/QuadraticBezierRepeater_Element.html
 	*  
 	**/	
	public class QuadraticBezierRepeater extends Repeater{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The Quadratic Bézier Repeater constructor accepts 6 optional arguments that define it's 
	 	* start, end and controls points, as well as 7 arguments for the repeat including count and offsets.</p>
	 	* 
	 	* @param x A number indicating the starting x-axis coordinate.
	 	* @param y A number indicating the starting y-axis coordinate.
	 	* @param cx A number indicating the control x-axis coordinate. 
	 	* @param cy A number indicating the control y-axis coordinate.
	 	* @param x1 A number indicating the ending x-axis coordinate.
	 	* @param y1 A number indicating the ending y-axis coordinate.
	 	* @param count A number indicating the repeat count of curves.
	 	* @param offsetX A number indicating the x-axis offset of each curve repeated.
	 	* @param offsetY A number indicating the y-axis offset of each curve repeated.
	 	* @param offsetX1 A number indicating the ending x-axis coordinate of each curve repeated. 
	 	* @param offsetY1 A number indicating the ending y-axis coordinate of each curve repeated.
		* @param offsetCx A number indicating the first control x-axis coordinate of each curve repeated.
		* @param offsetCy A number indicating the first control y-axis coordinate of each curve repeated.
	 	*/		
		public function QuadraticBezierRepeater(x:Number=0,y:Number=0,cx:Number=0,cy:Number=0,
		x1:Number=0,y1:Number=0,count:Number=0,offsetX:Number=0,offsetY:Number=0,offsetX1:Number=0,
		offsetY1:Number=0,offsetCx:Number=0,offsetCy:Number=0){
			
			super();
			
			this.x=x;
			this.y=y;
			this.cx=cx;
			this.cy=cy;
			this.x1=x1;
			this.y1=y1;
			
			super.count =count;
			super.offsetX=offsetX;
			super.offsetY=offsetY;
			
			this.offsetX1=offsetX1;
			this.offsetY1=offsetY1;
			this.offsetCx=offsetCx;
			this.offsetCy=offsetCy;
			
		}
		
		/**
		* QuadraticBezierRepeater short hand data value.
		* 
		* <p>The quadratic Bézier data property expects exactly 6 values x, 
		*  y, cx, cy, x1 and y1 separated by spaces.</p>
		* 
		* @see Repeater#data
		* 
		**/
		override public function set data(value:String):void{
			if(super.data != value){
				
				super.data = value;
				
				//parse the string on the space
				var tempArray:Array = value.split(" ");
				
				if (tempArray.length == 6){
					_x=tempArray[0];
					_y=tempArray[1];
					_cx=tempArray[2];
					_cy=tempArray[3];
					_x1=tempArray[4];
					_y1=tempArray[5];
				}	
				
				invalidated=true;
				
			}
		} 
				
		private var _offsetX1:Number=0;
		/**
		* The x-coordinate of the end point of the first repeated curves. If not specified 
		* a default value of 0 is used.
		**/
		public function get offsetX1():Number{
			return _offsetX1;
		}
		public function set offsetX1(value:Number):void{
			if(_offsetX1 != value){
				_offsetX1 = value;
				invalidated=true;
			}
			
		}
		
		private var _offsetY1:Number=0;
		/**
		* The y-coordinate of the end point of the repeated curves. If not specified 
		* a default value of 0 is used.
		**/
		public function get offsetY1():Number{
			return _offsetY1;
		}
		public function set offsetY1(value:Number):void{
			if(_offsetY1 != value){
				_offsetY1 = value;
				invalidated=true;
			}
		}
		
		private var _offsetCx:Number=0;
		/**
		* The x-coordinate of the first control point of the repeated curves. If not specified 
		* a default value of 0 is used.
		**/
		public function get offsetCx():Number{
			return _offsetCx;
		}
		public function set offsetCx(value:Number):void{
			if(_offsetCx != value){
				_offsetCx = value;
				invalidated=true;
			}
		}
		
		private var _offsetCy:Number=0;
		/**
		* The y-coordinate of the first control point of the repeated curves. If not specified 
		* a default value of 0 is used.
		**/
		public function get offsetCy():Number{
			return _offsetCy;
		}
		public function set offsetCy(value:Number):void{
			if(_offsetCy != value){
				_offsetCy = value;
				invalidated=true;
			}
		}
		
		private var _x:Number=0;
		/**
		* The x-coordinate of the start point of the first curve. If not specified 
		* a default value of 0 is used.
		**/
		public function get x():Number{
			return _x;
		}
		public function set x(value:Number):void{
			if(_x != value){
				_x = value;
				invalidated=true;
			}
		}
		
		private var _y:Number=0;
		/**
		* The y-coordinate of the start point of the first curve. If not specified 
		* a default value of 0 is used.
		**/
		public function get y():Number{
			return _y;
		}
		public function set y(value:Number):void{
			if(_y != value){
				_y = value;
				invalidated=true;
			}
		}
		
		private var _x1:Number=0;
		/**
		* The x-coordinate of the end point of the first curve. If not specified 
		* a default value of 0 is used.
		**/
		public function get x1():Number{
			return _x1;
		}
		public function set x1(value:Number):void{
			if(_x1 != value){
				_x1 = value;
				invalidated=true;
			}
		}
		
		private var _y1:Number=0;
		/**
		* The y-coordinate of the end point of the first curve. If not specified 
		* a default value of 0 is used.
		**/
		public function get y1():Number{
			return _y1;
		}
		public function set y1(value:Number):void{
			if(_y1 != value){
				_y1 = value;
				invalidated=true;
			}
		}
		
		private var _cx:Number=0;
		/**
		* The x-coordinate of the control point of the first curve. If not specified 
		* a default value of 0 is used.
		**/
		public function get cx():Number{
			return _cx;
		}
		public function set cx(value:Number):void{
			if(_cx != value){
				_cx = value;
				invalidated=true;
			}
		}
		
		private var _cy:Number=0;
		/**
		* The y-coordinate of the control point of the first curve. If not specified 
		* a default value of 0 is used.
		**/
		public function get cy():Number{			
			return _cy;
		}
		public function set cy(value:Number):void{
			if(_cy != value){
				_cy = value;
				invalidated=true;	
			}
		}
		
		/**
		* The tight bounds of this element as represented by a Rectangle object. 
		**/
		private var _bounds:Rectangle;
		public function get bounds():Rectangle{
			return _bounds;	
		}
		
		
		/**
		* Calculates the bounds for this element. 
		**/	
		private function calcBounds(unionRectangle:Rectangle):void{
			
			if(_bounds){
				_bounds = _bounds.union(unionRectangle);
			}
			else{
				_bounds = unionRectangle;
			}
		}	
		
		/**
		* @inheritDoc 
		**/
		override public function preDraw():void{
			if(invalidated){
				
				objectStack=[];
				_bounds = null;
				
				var newQuadraticBezier:QuadraticBezier;
				
	    	    //loop calc and add the circle for each count    	
	        	for (var i:int = 0;i< count;i++){	
	        		
	    			newQuadraticBezier = new QuadraticBezier(x,y,cx,cy,x1,y1);
	    		  	
	    		  	newQuadraticBezier.stroke = stroke;
	    			newQuadraticBezier.fill = fill;
	        		
	        		newQuadraticBezier.x1=x1+(i*offsetX1);
	        		newQuadraticBezier.y1=y1+(i*offsetY1);
	        		newQuadraticBezier.cx=cx+(i*offsetCx);
		        	newQuadraticBezier.cy=cy+(i*offsetCy);
		        	newQuadraticBezier.x=x+(i*offsetX);
	        		newQuadraticBezier.y=y+(i*offsetY);	
					
					
					//add to the bounds
					newQuadraticBezier.preDraw();
        			calcBounds(newQuadraticBezier.bounds);
        		
        			objectStack.push(newQuadraticBezier);
        		
				}
			
				invalidated=false;	
			}
		}
		
		/**
		* An Array of geometry objects that make up this repeater. 
		**/
		protected var objectStack:Array=[];	
		
		/**
		* Begins the draw phase for geometry objects. All geometry objects 
		* override this to do their specific rendering.
		* 
		* @param graphics The current context to draw to.
		* @param rc A Rectangle object used for fill bounds. 
		**/		
		override public function draw(graphics:Graphics,rc:Rectangle):void{		
					        	
        	preDraw();
			
        	var item:QuadraticBezier;
        	
        	for each (item in objectStack){
        		//draw the item
				item.draw(graphics,rc);
			}
        	        	        	
        }
	}
}