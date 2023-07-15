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
	
	import com.degrafa.geometry.CubicBezier;
	
	import flash.display.Graphics;
	import flash.geom.Rectangle;
	
	[Bindable]
	/**
 	*  The CubicBezierRepeater element draws a cubic Bézier using the specified 
 	*  start point, end point and 2 control points. Then progressivly repeats the 
 	*  cubic bézier using the specified count and offsets.
 	*  
 	*  @see http://samples.degrafa.com/CubicBezierRepeater_Element.html
 	*  
 	**/	
	public class CubicBezierRepeater extends Repeater{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The Cubic Bézier Repeater constructor accepts 17 optional arguments that define it's 
	 	* start, end and controls points.</p>
	 	* 
	 	* @param x A number indicating the starting x-axis coordinate.
	 	* @param y A number indicating the starting y-axis coordinate.
	 	* @param cx A number indicating the first control x-axis coordinate. 
	 	* @param cy A number indicating the first control y-axis coordinate.
	 	* @param cx1 A number indicating the second control x-axis coordinate.
	 	* @param cy1 A number indicating the second control y-axis coordinate.
	 	* @param x1 A number indicating the ending x-axis coordinate.
	 	* @param y1 A number indicating the ending y-axis coordinate. 
	 	* @param count A number indicating the number of times the cubic bézier is repeated.
	 	* @param offsetX A number indicating the starting x-axis coordinate offset of each repeated cubic bézier. 
	 	* @param offsetY A number indicating the starting y-axis coordinate offset of each repeated cubic bézier.
	 	* @param offsetX1 A number indicating the ending x-axis coordinate offset of each repeated cubic bézier. 
	 	* @param offsetY1 A number indicating the ending y-axis coordinate offset of each repeated cubic bézier.
		* @param offsetCx A number indicating the first control x-axis coordinate offset of each repeated cubic bézier.
		* @param offsetCy A number indicating the first control y-axis coordinate offset of each repeated cubic bézier.  
		* @param offsetCx1 A number indicating the second control x-axis coordinate offset of each repeated cubic bézier.
		* @param offsetCy1 A number indicating the second control y-axis coordinate offset of each repeated cubic bézier.  
	 	*/
		public function CubicBezierRepeater(x:Number=0,y:Number=0,cx:Number=0,cy:Number=0,
		cx1:Number=0,cy1:Number=0,x1:Number=0,y1:Number=0,count:Number=0,offsetX:Number=0,
		offsetY:Number=0,offsetX1:Number=0,offsetY1:Number=0,offsetCx:Number=0,offsetCy:Number=0
		,offsetCx1:Number=0,offsetCy1:Number=0){
			
			super();
			
			this.x=x;
			this.y=y;
			this.cx=cx;
			this.cy=cy;
			this.cx1=cx1;
			this.cy1=cy1;
			this.x1=x1;
			this.y1=y1;
			
			super.count = count;
			super.offsetX=offsetX;
			super.offsetY=offsetY;
			
			this.offsetX1=offsetX1;
			this.offsetY1=offsetY1;
			this.offsetCx=offsetCx;
			this.offsetCy=offsetCy;
			this.offsetCx1=offsetCx1;
			this.offsetCy1=offsetCy1;
		
			
		}
		
		/**
		* CubicBezierRepeater short hand data value.
		* 
		* <p>The Cubic Bézier Repeater data property expects exactly 8 values x, 
		*  y, cx, cy, cx1, cy1, x1 and y1 separated by spaces.</p>
		* 
		* @see Repeater#data
		* 
		**/
		override public function set data(value:String):void{
			if(super.data != value){
				super.data = value;
			
				//parse the string on the space
				var tempArray:Array = value.split(" ");
				
				if (tempArray.length == 8){
					_x=tempArray[0];
					_y=tempArray[1];
					_cx=tempArray[2];
					_cy=tempArray[3];
					_cx1=tempArray[4];
					_cy1=tempArray[5];
					_x1=tempArray[6];
					_y1=tempArray[7];
				}	
				
				invalidated=true;
				
			}
		} 
				
		private var _offsetX1:Number=0;
		/**
		* The x-coordinate of the end point of the repeated curves. If not specified 
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
		
		private var _offsetCx1:Number=0;
		/**
		* The x-coordinate of the second control point of the repeated curves. If not specified 
		* a default value of 0 is used.
		**/
		public function get offsetCx1():Number{
			return _offsetCx1;
		}
		public function set offsetCx1(value:Number):void{
			if(_offsetCx1 != value){
				_offsetCx1 = value;
				invalidated=true;
			}
			
		}
		
		private var _offsetCy1:Number=0;
		/**
		* The y-coordinate of the second control point of the repeated curves. If not specified 
		* a default value of 0 is used.
		**/public function get offsetCy1():Number{
			return _offsetCy1;
		}
		public function set offsetCy1(value:Number):void{
			if(_offsetCy1 != value){
				_offsetCy1 = value;
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
		* The x-coordinate of the start point of the first curve. If not specified 
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
		* The x-coordinate of the first control point of the first curve. If not specified 
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
		* The y-coordinate of the first control point of the first curve. If not specified 
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
		
		private var _cx1:Number=0;
		/**
		* The x-coordinate of the second control point of the first curve. If not specified 
		* a default value of 0 is used.
		**/
		public function get cx1():Number{
			return _cx1;
		}
		public function set cx1(value:Number):void{
			if(_cx1 != value){
				_cx1 = value;
				invalidated=true;
			}
		}
		
		private var _cy1:Number=0;
		/**
		* The y-coordinate of the second control point of the first curve. If not specified 
		* a default value of 0 is used.
		**/
		public function get cy1():Number{
			return _cy1;
		}
		public function set cy1(value:Number):void{
			if(_cy1 != value){
				_cy1 = value;
				invalidated=true;	
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
				
				var newCubicBezier:CubicBezier;
				
	    	    //loop calc and add the circle for each count    	
	        	for (var i:int = 0;i< count;i++){	
	        		
	        		newCubicBezier = new CubicBezier(x,y,cx,cy,cx1,cy1,x1,y1);
	        		
	        		newCubicBezier.stroke = stroke;
    				newCubicBezier.fill = fill;
    		
	    			newCubicBezier.x1=x1+(i*offsetX1);
        			newCubicBezier.y1=y1+(i*offsetY1);
        			newCubicBezier.cx=cx+(i*offsetCx);
	        		newCubicBezier.cy=cy+(i*offsetCy);
	        		newCubicBezier.cx1=cx1+(i*offsetCx1);
	        		newCubicBezier.cy1=cy1+(i*offsetCy1);
        			newCubicBezier.x=x+(i*offsetX);
        			newCubicBezier.y=y+(i*offsetY);	
					
					
					//add to the bounds
					newCubicBezier.preDraw();
					
        			calcBounds(newCubicBezier.bounds);
        		
        			objectStack.push(newCubicBezier);
        		
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
			
        	var item:CubicBezier;
        	
        	for each (item in objectStack){
        		//draw the item
				item.draw(graphics,rc);
			}
        	
			
		}		
	}
}