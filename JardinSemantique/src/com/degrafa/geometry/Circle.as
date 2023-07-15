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
	import flash.display.Graphics;
	import flash.geom.Rectangle;
	
	[Bindable]	
	/**
 	*  The Circle element draws a circle using the specified center point 
 	*  and radius.
 	*  
 	*  @see http://samples.degrafa.com/Circle/Circle.html	    
 	* 
 	**/
	public class Circle extends Geometry implements IGeometry{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The circle constructor accepts 3 optional arguments that define it's 
	 	* center point and radius.</p>
	 	* 
	 	* @param centerX A number indicating the center x-axis coordinate.
	 	* @param centerY A number indicating the center y-axis coordinate.
	 	* @param radius A number indicating the radius of the circle. 
	 	*/		
		public function Circle(centerX:Number=0,centerY:Number=0,radius:Number=0){			
			super();
			
			this.centerX=centerX;
			this.centerY=centerY;
			this.radius=radius;
		}
		
		/**
		* Circle short hand data value.
		* 
		* <p>The circle data property expects exactly 3 values centerX, 
		* centerY and radius separated by spaces.</p>
		* 
		* @see Geometry#data
		* 
		**/
		override public function set data(value:String):void{
			if(super.data != value){
				super.data = value;
			
				//parse the string
				var tempArray:Array = value.split(" ");
				
				if (tempArray.length == 3)
				{
					_centerX=tempArray[0];
					_centerY=tempArray[1];
					_radius=tempArray[2];
				}	
				
				invalidated = true;
			}
		} 
		
		 
		private var _centerX:Number=0;
		/**
		* The x-axis coordinate of the center of the circle. If not specified 
		* a default value of 0 is used.
		**/
		public function get centerX():Number{
			return _centerX;
		}
		public function set centerX(value:Number):void{
			if(_centerX != value){
				_centerX = value;
				invalidated = true;
			}
		}
				
		private var _centerY:Number=0;
		/**
		* The y-axis coordinate of the center of the circle. If not specified 
		* a default value of 0 is used.
		**/
		public function get centerY():Number{
			return _centerY;
		}
		public function set centerY(value:Number):void{
			if(_centerY != value){
				_centerY = value;
				invalidated = true;
			}
			
		}
		
						
		private var _radius:Number=0;
		/**
		* The radius of the circle. If not specified a default value of 0 
		* is used.
		**/
		public function get radius():Number{
			return _radius;
		}
		public function set radius(value:Number):void{
			if(_radius != value){
				_radius = value;
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
			_bounds = new Rectangle(centerX-radius,centerY-radius,radius*2,radius*2);
		}		
		
		
		/**
		* @inheritDoc 
		**/
		override public function preDraw():void{
			if(invalidated){
			
				commandStack = [];
				
				commandStack.push({type:"circle", centerX:centerX,
					centerY:centerY,
					radius:radius
					});	
				
			
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
        		
        		graphics.drawCircle(item.centerX,item.centerY,
        		item.radius);
        		
        	}
				 	 		 	 	
	 	 	super.endDraw(graphics);
					
		}
				
	}
}