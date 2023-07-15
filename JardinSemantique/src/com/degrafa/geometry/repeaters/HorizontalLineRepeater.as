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
	
	import com.degrafa.core.IGraphicsFill;
	import com.degrafa.geometry.HorizontalLine;
	
	import flash.display.Graphics;
	import flash.geom.Rectangle;
	 
	[Exclude(name="fill", kind="property")]
	[Bindable]
	/**
 	*  The HorizontalLineRepeater element draws a horizontal line using the specified x, y, 
 	*  and x1 coordinate values. Then progressivly repeats the horizontal line using 
 	*  the specified count and offsets.
 	*  
 	*  @see http://samples.degrafa.com/HorizontalLineRepeater/HorizontalLineRepeater.html	    
 	* 
 	**/		
	public class HorizontalLineRepeater extends Repeater{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The horizontal line constructor accepts 8 optional arguments that define it's 
	 	* center point and radius.</p>
	 	* 
	 	* @param x A number indicating the starting x-axis coordinate.
	 	* @param y A number indicating the starting y-axis coordinate.
	 	* @param x1 A number indicating the ending x-axis coordinate.
	 	* @param count A number indicating the repeat count of horizontal lines.
	 	* @param offsetX A number indicating the x-axis offset of each horizontal line repeated.
	 	* @param offsetY A number indicating the y-axis offset of each horizontal line repeated.
	 	* @param moveOffsetX A number indicating the x-axis of each horizontal line repeated.
	 	* @param moveOffsetY A number indicating the y-axis of each horizontal line repeated.
	 	*/
		public function HorizontalLineRepeater(x:Number=0,y:Number=0,x1:Number=0,
		count:Number=0,offsetX:Number=0,offsetY:Number=0,moveOffsetX:Number=0,
		moveOffsetY:Number=0){
			
			super();
			
			this.x=x;
			this.y=y;
			this.x1=x1;
			
			super.count =count;
			super.offsetX=offsetX;
			super.offsetY=offsetY;
			
			this.moveOffsetX=moveOffsetX;
			this.moveOffsetY=moveOffsetY;
			
		}
		
		/**
		* HorizontalLineRepeater short hand data value.
		* 
		* <p>The Horizontal Line Repeater data property expects exactly 3 values x, 
		* y and x1 separated by spaces.</p>
		* 
		* @see Repeater#data
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
				
				invalidated=true;
				
			}
		}  
		
				
		private var _moveOffsetX:Number=0;
		/**
		* The x-axis offset of each repeated line. If not specified 
		* a default value of 0 is used.
		**/
		public function get moveOffsetX():Number{
			return _moveOffsetX;
		}
		public function set moveOffsetX(value:Number):void{
			if(_moveOffsetX != value){
				_moveOffsetX = value;
				invalidated=true;
			}
		}

		private var _moveOffsetY:Number=0;
		/**
		* The y-axis offset of each repeated line. If not specified 
		* a default value of 0 is used.
		**/
		public function get moveOffsetY():Number{
			return _moveOffsetY;
		}
		public function set moveOffsetY(value:Number):void{
			if(_moveOffsetY != value){
				_moveOffsetY = value;
				invalidated=true;
			}
		}
				
		private var _x:Number=0;
		/**
		* The x-coordinate of the start point of the first line. If not specified 
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
		* The y-coordinate of the start point of the first line. If not specified 
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
		* The x-coordinate of the end point of the first line. If not specified 
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
				
				var newHorizontalLine:HorizontalLine;
				
	    	    //loop calc and add the circle for each count    	
	        	for (var i:int = 0;i< count;i++){	
	        		
	    			newHorizontalLine = new HorizontalLine(x,y,x1);
	    		  	
	    		  	newHorizontalLine.stroke = stroke;
	    			newHorizontalLine.fill = fill;
	        		
	        		newHorizontalLine.x = x + (i*moveOffsetX);
        			newHorizontalLine.y = y + (i*moveOffsetY);
        			newHorizontalLine.x1 = x1 + (i*offsetX);
        	
					
					
					//add to the bounds
					newHorizontalLine.preDraw();
        			calcBounds(newHorizontalLine.bounds);
        		
        			objectStack.push(newHorizontalLine);
        		
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
			
        	var item:HorizontalLine;
        	
        	for each (item in objectStack){
        		//draw the item
				item.draw(graphics,rc);
			}    
        							
			
		}
		
	}
}