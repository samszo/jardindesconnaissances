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
	
	import com.degrafa.geometry.Circle;
	
	import flash.display.Graphics;
	import flash.geom.Rectangle;
	
	[Bindable]
	/**
 	*  The CircleRepeater element draws a circle using the specified center point 
 	*  and radius. Then progressivly repeats the circle using
 	*  the specified count and offsets.
 	*  
 	*  @see http://samples.degrafa.com/CircleRepeater/CircleRepeater.html   
 	* 
 	**/
	public class CircleRepeater extends Repeater{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The circle repeater constructor accepts 7 optional arguments that 
	 	* define it's center point, radius, repeat count and offsets.</p>
	 	* 
	 	* @param centerX A number indicating the center x-axis coordinate.
	 	* @param centerY A number indicating the center y-axis coordinate.
	 	* @param radius A number indicating the radius of the circle. 
	 	* @param count A number indicating the repeat count of circles.
	 	* @param offsetRadius A number indicating the offset radius of each circle repeated.
	 	* @param offsetX A number indicating the x-axis coordinate offset of each circle repeated.
	 	* @param offsetY A number indicating the y-axis coordinate offset of each circle repeated.   
	 	*/
		public function CircleRepeater(centerX:Number=0,centerY:Number=0,radius:Number=0,
		count:Number=0,offsetRadius:Number=0,offsetX:Number=0,offsetY:Number=0){
			
			super();
			
			this.centerX=centerX;
			this.centerY=centerY;
			this.radius=radius;
			
			this.offsetRadius = offsetRadius;
			
			super.count =count;
			super.offsetX=offsetX;
			super.offsetY=offsetY;
			
		}
		
		/**
		* Circle Repeater short hand data value.
		* 
		* <p>The circle repeater data property expects exactly 3 values centerX, 
		* centerY, and radius separated by spaces.</p>
		* 
		* @see Repeater#data
		* 
		**/
		override public function set data(value:String):void{
			if(super.data != value){
				super.data = value;
			
				//parse the string on the space
				var tempArray:Array = value.split(" ");
				
				if (tempArray.length == 3)
				{
					_centerX=tempArray[0];
					_centerY=tempArray[1];
					_radius=tempArray[2];
				}	
				
				invalidated=true;
			}
			
		} 
				
		private var _centerX:Number=0;
		/**
		* The x-axis coordinate of the center of the first circle. If not specified 
		* a default value of 0 is used.
		**/
		public function get centerX():Number{
			return _centerX;
		}
		public function set centerX(value:Number):void{
			if(_centerX != value){
				_centerX = value;
				invalidated=true;
			}
		}
		
		private var _centerY:Number=0;
		/**
		* The y-axis coordinate of the center of the first circle. If not specified 
		* a default value of 0 is used.
		**/
		public function get centerY():Number{
			return _centerY;
		}
		
		public function set centerY(value:Number):void{
			if(_centerY != value){
				_centerY = value;
				invalidated=true;
			}
			
		}
						
		private var _radius:Number=0;
		/**
		* The radius of the first circle. If not specified a default value of 0 
		* is used.
		**/
		public function get radius():Number
		{
			return _radius;
		}
		
		public function set radius(value:Number):void{
			if(_radius != value){
				_radius = value;
				invalidated=true;	
			}
		}
		
		private var _offsetRadius:Number=0;
		/**
		* The offset radius of each circle in repeater. If not specified a default  
		* value of 0 is used.
		**/
		public function get offsetRadius():Number{
			return _offsetRadius;
		}
		public function set offsetRadius(value:Number):void{
			if(_offsetRadius != value){
				_offsetRadius = value;
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
				
				var newCircle:Circle;
				
	    	    //loop calc and add the circle for each count    	
	        	for (var i:int = 0;i< count;i++){	
	        		
	    			newCircle = new Circle(_centerX,_centerY,_radius);
	    		  	
	    		  	newCircle.stroke = stroke;
	    			newCircle.fill = fill;
	        		
	        		newCircle.radius = radius + (i*offsetRadius);
	        		newCircle.centerX = centerX + (i*offsetX);
	        		newCircle.centerY = centerY + (i*offsetY);
					
					
					//add to the bounds
					newCircle.preDraw();
        			calcBounds(newCircle.bounds);
        		
        			objectStack.push(newCircle);
        		
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
			
        	var item:Circle;
        	
        	for each (item in objectStack){
        		//draw the item
				item.draw(graphics,rc);
			}
        	        	
		}
				
	}
}