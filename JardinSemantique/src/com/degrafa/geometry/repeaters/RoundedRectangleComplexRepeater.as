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
	
	import com.degrafa.geometry.RoundedRectangleComplex;
	
	import flash.display.Graphics;
	import flash.geom.Rectangle;
	
	[Bindable]
	/**
 	*  The RoundedRectangleComplex element draws a complex rounded rectangle using the specified x,y,
 	*  width, height and top left radius, top right radius, bottom left radius and bottom right 
 	*  radius. Then progressively repeats the rectangle using count and offsets.
 	*  
 	*  @see http://samples.degrafa.com/RoundedRectangleComplexRepeater/RoundedRectangleComplexRepeater.html
 	*  
 	**/	
	public class RoundedRectangleComplexRepeater extends Repeater{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The complex rounded rectangle repeater constructor accepts 8 optional arguments that define it's 
	 	* x, y, width, height, top left radius, top right radius, bottom left radius 
	 	* and bottom right radius; as well as 9 arguments for count and offsets. </p>
	 	* 
	 	* @param x A number indicating the upper left x-axis coordinate.
	 	* @param y A number indicating the upper left y-axis coordinate.
	 	* @param width A number indicating the width.
	 	* @param height A number indicating the height. 
	 	* @param topLeftRadius A number indicating the top left corner radius.
	 	* @param topRightRadius A number indicating the top right corner radius.
	 	* @param bottomLeftRadius A number indicating the bottom left corner radius.
	 	* @param bottomRightRadius A number indicating the bottom right corner radius.
	 	* @param offsetTopLeftRadius A number indicating the left corner radius offset of each complex rounded rectangle repeated.
	 	* @param offsetTopRightRadius A number indicating the top right corner radius offset of each complex rounded rectangle repeated.
	 	* @param offsetBottomLeftRadius A number indicating the bottom left corner radius offset of each complex rounded rectangle repeated.
	 	* @param offsetBottomRightRadius A number indicating the bottom right corner radius offset of each complex rounded rectangle repeated.
	 	* @param count A number indicating the repeat count of complex rounded rectangles.
	 	* @param offsetX A number indicating the x-axis offset of each complex rounded rectangle repeated.
	 	* @param offsetY A number indicating the y-axis offset of each complex rounded rectangle repeated.
	 	* @param offsetWidth A number indicating the width offset of each complex rounded rectangle repeated.
	 	* @param offsetHeight A number indicating the height offset of each complex rounded rectangle repeated.
	 	*/
		public function RoundedRectangleComplexRepeater(x:Number=0,y:Number=0,
		width:Number=0,height:Number=0,topLeftRadius:Number=0,topRightRadius:Number=0,
		bottomLeftRadius:Number=0,bottomRightRadius:Number=0,count:Number=0,
		offsetX:Number=0,offsetY:Number=0,offsetTopLeftRadius:Number=0,
		offsetTopRightRadius:Number=0,offsetBottomLeftRadius:Number=0, 
		offsetBottomRightRadius:Number=0,offsetWidth:Number=0,offsetHeight:Number=0){
			super();
			
			this.x=x;
			this.y=y;
			this.width=width;
			this.height=height;
			this.topLeftRadius=topLeftRadius;
			this.topRightRadius=topRightRadius;
			this.bottomLeftRadius=bottomLeftRadius;
			this.bottomRightRadius=bottomRightRadius;
			
			super.count =count;
			super.offsetX=offsetX;
			super.offsetY=offsetY;
			
			this.offsetTopLeftRadius=offsetTopLeftRadius;
			this.offsetTopRightRadius=offsetTopRightRadius;
			this.offsetBottomLeftRadius=offsetBottomLeftRadius;
			this.offsetBottomRightRadius=offsetBottomRightRadius;
			this.offsetWidth=offsetWidth;
			this.offsetHeight=offsetHeight;
				
		}
		
		/**
		* RoundedRectangleComplex short hand data value.
		* 
		* <p>The complex rounded rectangle data property expects exactly 8 values x, 
		* y, width, height, top left radius, top right radius, bottom left radius 
	 	* and bottom right radius separated by spaces.</p>
		* 
		* @see Geometry#data
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
					_width=tempArray[2];
					_height=tempArray[3];
					_topLeftRadius=tempArray[4];
					_topRightRadius=tempArray[5];
					_bottomLeftRadius=tempArray[6];
					_bottomRightRadius=tempArray[7];
				}	
			}
		} 		
		
		
		private var _offsetTopLeftRadius:Number=0;
		/**
		* The offset of the upper left radius of the complex rounded rectangle. If not specified 
		* a default value of 0 is used.
		**/
		public function get offsetTopLeftRadius():Number{
			return _offsetTopLeftRadius;
		}
		public function set offsetTopLeftRadius(value:Number):void{
			if(_offsetTopLeftRadius != value){
				_offsetTopLeftRadius = value;
				invalidated=true;
			}
			
		}
		
		private var _offsetTopRightRadius:Number=0;
		/**
		* The offset of the upper right radius of the complex rounded rectangle. If not specified 
		* a default value of 0 is used.
		**/
		public function get offsetTopRightRadius():Number{
			return _offsetTopRightRadius;
		}
		public function set offsetTopRightRadius(value:Number):void{
			if(_offsetTopRightRadius != value){
				_offsetTopRightRadius = value;
				invalidated=true;
			}
			
		}
		
		private var _offsetBottomLeftRadius:Number=0;
		/**
		* The offset of the lower left radius of the complex rounded rectangle. If not specified 
		* a default value of 0 is used.
		**/
		public function get offsetBottomLeftRadius():Number{
			return _offsetBottomLeftRadius;
		}
		public function set offsetBottomLeftRadius(value:Number):void{
			if(_offsetBottomLeftRadius != value){
				_offsetBottomLeftRadius = value;
				invalidated=true;
			}
			
		}
		
		private var _offsetBottomRightRadius:Number=0;
		/**
		* The offset of the lower right radius of the complex rounded rectangle. If not specified 
		* a default value of 0 is used.
		**/
		public function get offsetBottomRightRadius():Number{
			return _offsetBottomRightRadius;
		}
		public function set offsetBottomRightRadius(value:Number):void{			
			if(_offsetBottomRightRadius != value){
				_offsetBottomRightRadius = value;
				invalidated=true;
			}
			
		}
				
		private var _offsetWidth:Number=0;
		/**
		* The offsetWidth value sets the width of the offset of the repeated rectangles. 
		* If not specified a default value of 0 is used.
		**/
		public function get offsetWidth():Number{
			return _offsetWidth;
		}
		public function set offsetWidth(value:Number):void{			
			if(_offsetWidth != value){
				_offsetWidth = value;
				invalidated=true;
			}
		}
		
		private var _offsetHeight:Number=0;
		/**
		* The offsetHeight value sets the width of the offset of the repeated rectangles. 
		* If not specified a default value of 0 is used.
		**/
		public function get offsetHeight():Number{
			return _offsetHeight;
		}
		public function set offsetHeight(value:Number):void{			
			if(_offsetHeight != value){
				_offsetHeight = value;
				invalidated=true;
			}
		}
		
		private var _x:Number=0;
		/**
		* The x-axis coordinate of the upper left point of the first complex rounded rectangle. If not specified 
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
		* The y-axis coordinate of the upper left point of the first complex rounded rectangle. If not specified 
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
						
		private var _width:Number=0;
		/**
		* The width of the first complex rounded rectangle.
		**/
		public function get width():Number{
			return _width;
		}
		public function set width(value:Number):void{
			if(_width != value){
				_width = value;
				invalidated=true;
			}
		}
		
		private var _height:Number=0;
		/**
		* The height of the first complex rounded rectangle.
		**/
		public function get height():Number{
			return _height;
		}
		public function set height(value:Number):void{
			if(_height != value){
				_height = value;
				invalidated=true;
			}
		}
		
		private var _topLeftRadius:Number=0;
		/**
		* The radius for the top left corner of the first complex rounded rectangle.
		**/
		public function get topLeftRadius():Number{
			return _topLeftRadius;
		}
		public function set topLeftRadius(value:Number):void{
			if(_topLeftRadius != value){
				_topLeftRadius = value;
				invalidated=true;
			}
		}
		
		private var _topRightRadius:Number=0;
		/**
		* The radius for the top right corner of the first complex rounded rectangle.
		**/
		public function get topRightRadius():Number{
			return _topRightRadius;
		}
		public function set topRightRadius(value:Number):void{
			if(_topRightRadius != value){
				_topRightRadius = value;
				invalidated=true;
			}
		}
		
		private var _bottomLeftRadius:Number=0;
		/**
		* The radius for the bottom left corner of the first complex rounded rectangle.
		**/
		public function get bottomLeftRadius():Number{			
			return _bottomLeftRadius;
		}
		public function set bottomLeftRadius(value:Number):void{
			if(_bottomLeftRadius != value){
				_bottomLeftRadius = value;
				invalidated=true;
			}
		}
		
		private var _bottomRightRadius:Number=0;
		/**
		* The radius for the bottom right corner of the first complex rounded rectangle.
		**/
		public function get bottomRightRadius():Number{
			return _bottomRightRadius;
		}
		public function set bottomRightRadius(value:Number):void{
			if(_bottomRightRadius != value){
				_bottomRightRadius = value;
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
				
				var newRoundedRectangleComplex:RoundedRectangleComplex;
				
	    	    //loop calc and add the circle for each count    	
	        	for (var i:int = 0;i< count;i++){	
	        		
	    			newRoundedRectangleComplex = new RoundedRectangleComplex(x,
    				y,width,height,topLeftRadius,topRightRadius,bottomLeftRadius,bottomRightRadius);
    		    		
	    		  	newRoundedRectangleComplex.stroke = stroke;
	    			newRoundedRectangleComplex.fill = fill;
	        		
		        	newRoundedRectangleComplex.x = x + (i*offsetX);
	        		newRoundedRectangleComplex.y = y + (i*offsetY);
	        		newRoundedRectangleComplex.width = width + (i*offsetWidth);
	        		newRoundedRectangleComplex.height = height + (i*offsetHeight);
	        		
	        		newRoundedRectangleComplex.topLeftRadius=topLeftRadius+(i*offsetTopLeftRadius);
	        		newRoundedRectangleComplex.topRightRadius = topRightRadius+(i*offsetTopRightRadius);
	        		newRoundedRectangleComplex.bottomLeftRadius =bottomLeftRadius+(i*offsetBottomLeftRadius);
	        		newRoundedRectangleComplex.bottomRightRadius =bottomRightRadius+(i*offsetBottomRightRadius);
	        		
        		
					//add to the bounds
					newRoundedRectangleComplex.preDraw();
        			calcBounds(newRoundedRectangleComplex.bounds);
        		
        			objectStack.push(newRoundedRectangleComplex);
        		
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
			
        	var item:RoundedRectangleComplex;
        	
        	for each (item in objectStack){
        		//draw the item
				item.draw(graphics,rc);
			}
			
			
        }
	}
}