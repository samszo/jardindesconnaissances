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
	
	import com.degrafa.geometry.EllipticalArc;
	
	import flash.display.Graphics;
	import flash.geom.Rectangle;
	
	[Bindable]
	/**
 	*  The EllipticalArcRepeater element draws an elliptical arc using the specified
 	*  x, y, width, height, start angle, arc and closure type. Then progressivly 
 	*  repeats the elliptical arc using the specified count and offsets.
 	*  
 	*  @see http://samples.degrafa.com/EllipticalArcRepeater_Element.html	    
 	* 
 	**/
	public class EllipticalArcRepeater extends Repeater{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The Elliptical Arc Repeater constructor accepts 14 optional arguments that define it's 
	 	* x, y, width, height, start angle, arc and closure type.</p>
	 	* 
	 	* @param x A number indicating the upper left x-axis coordinate.
	 	* @param y A number indicating the upper left y-axis coordinate.
	 	* @param width A number indicating the width.
	 	* @param height A number indicating the height. 
	 	* @param startAngle A number indicating the beginning angle of the arc.
	 	* @param arc A number indicating the angular extent of the arc, relative to the start angle.
	 	* @param closureType A string indicating the method used to close the arc. 
	 	* @param count A number indicating the repeat count of arcs. 
	 	* @param offsetX A number indicating the starting x-axis coordinate offset of each arc repeated. 
	 	* @param offsetY A number indicating the starting y-axis coordinate offset of each arc repeated. 
	 	* @param offsetWidth A number indicating the width offset of each arc repeated.
	 	* @param offsetHeight A number indicating the height offset of each arc repeated.
	 	* @param offsetStartAngle A number indicating the starting angle offset of each arc repeated.
	 	* @param offsetArc A number indicating the offset of each arc repeated. 
	 	*/
		public function EllipticalArcRepeater(x:Number=0,y:Number=0,width:Number=0,
		height:Number=0,startAngle:Number=0,arc:Number=0,closureType:String="open",
		count:Number=0,offsetX:Number=0,offsetY:Number=0,offsetWidth:Number=0,
		offsetHeight:Number=0,offsetStartAngle:Number=0,offsetArc:Number=0){
			
			super();
			this.x=x;
			this.y=y;
			this.width=width; 
			this.height=height;
			this.startAngle=startAngle;
			this.arc=arc;
			this.closureType=closureType;
			
			super.count =count;
			super.offsetX=offsetX;
			super.offsetY=offsetY;
			
			this.offsetWidth =offsetWidth; 
			this.offsetHeight=offsetHeight;
			this.offsetStartAngle=offsetStartAngle;
			this.offsetArc=offsetArc;
			
		}
		
		
		
		/**
		* EllipticalArcRepeater short hand data value.
		* 
		* <p>The Elliptical Arc Repeater data property expects exactly 6 values x, 
		* y, width, height, startAngle, arc and closureType separated by spaces.</p>
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
					_width=tempArray[2]; //radius
					_height=tempArray[3]; //yRadius
					_startAngle=tempArray[4]; //angle
					_arc=tempArray[5]; //extent
					
				}	
				
				invalidated=true;
				
			}
				
		} 
				
		private var _offsetWidth:Number=0;
		/**
		* The width offset of each repeated arc. If not specified 
		* a default value of 0 is used.
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
		* The height offset of each repeated arc. If not specified 
		* a default value of 0 is used.
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
		
		private var _offsetStartAngle:Number=0;
		/**
		* The starting angle offset of each repeated arc. If not specified 
		* a default value of 0 is used.
		**/
		public function get offsetStartAngle():Number{
			return _offsetStartAngle;
		}
		public function set offsetStartAngle(value:Number):void{
			if(_offsetStartAngle != value){
				_offsetStartAngle = value;
				invalidated=true;
			}
		}
		
		private var _offsetArc:Number=0;
		/**
		* The arc offset of each repeated arc. If not specified 
		* a default value of 0 is used.
		**/
		public function get offsetArc():Number
		{
			return _offsetArc;
		}
		public function set offsetArc(value:Number):void{
			if(_offsetArc != value){
				_offsetArc = value;
				invalidated=true;
			}
		}
		
		private var _startAngle:Number=0;
		/**
		* The starting angle of the first arc. If not specified 
		* a default value of 0 is used.
		**/
		public function get startAngle():Number{
			return _startAngle;
		}
		public function set startAngle(value:Number):void{
			if(_startAngle != value){
				_startAngle = value;
				invalidated=true;
			}
		}
		
		private var _arc:Number=0;
		/**
		* The angular extent of the first arc, relative to the start angle. If not specified 
		* a default value of 0 is used.
		**/
		public function get arc():Number{
			return _arc;
		}
		public function set arc(value:Number):void{
			if(_arc != value){
				_arc = value;
				invalidated=true;
			}
		}
		
		private var _x:Number=0;
		/**
		* The upper left x-axis coordinate of the first arc. If not specified 
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
		* The upper left y-axis coordinate of the first arc. If not specified 
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
		* The width of the first arc. If not specified a default value of 0 is used.
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
		* The height of the first arc. If not specified a default value of 0 is used.
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
		
		private var _closureType:String="open";
		[Inspectable(category="General", enumeration="open,chord,pie", defaultValue="open")]
		/**
		* The method in which to close the arc.
		* <p>
		* <li><b>open</b> will apply no closure.</li>
		* <li><b>chord</b> will close the arc with a strait line to the start.</li>
		* <li><b>pie</b> will draw a line from center to start and end to center forming a pie shape.</li>
		* </p> 
		**/
		public function set closureType(value:String):void{			
			if(_closureType != value){
				_closureType = value;
				invalidated=true;	
			}
			
		}
		public function get closureType():String{
			return _closureType;
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
				
				var newEllipticalArc:EllipticalArc;
				
	    	    //loop calc and add the circle for each count    	
	        	for (var i:int = 0;i< count;i++){	
	        		
	    			newEllipticalArc = new EllipticalArc(x,y,width,height,startAngle,arc,closureType);
	    		  	
	    		  	newEllipticalArc.stroke = stroke;
	    			newEllipticalArc.fill = fill;
	        		
	        		newEllipticalArc.width = width+(i*(offsetWidth));
        			newEllipticalArc.height=height+(i*(offsetHeight));
        			newEllipticalArc.arc=arc+(i*offsetArc);
	        		newEllipticalArc.startAngle=startAngle+(i*offsetStartAngle);
        			newEllipticalArc.x=x+(i*offsetX);
        			newEllipticalArc.y=y+(i*offsetY);
					
					//add to the bounds
					newEllipticalArc.preDraw();
        			calcBounds(newEllipticalArc.bounds);
        		
        			objectStack.push(newEllipticalArc);
        		
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
			
        	var item:EllipticalArc;
        	
        	for each (item in objectStack){
        		//draw the item
				item.draw(graphics,rc);
			}   

			        	
		}
				
	}
}