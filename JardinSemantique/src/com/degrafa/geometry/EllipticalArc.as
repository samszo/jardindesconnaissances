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
	import com.degrafa.geometry.utilities.ArcUtils;
	
	import flash.display.Graphics;
	import flash.geom.Rectangle;
	
	
	[Bindable]	
	/**
 	*  The EllipticalArc element draws an elliptical arc using the specified
 	*  x, y, width, height, start angle, arc and closure type.
 	*  
 	*  @see http://degrafa.com/samples/EllipticalArc_Element.html
 	*  
 	**/	
	public class EllipticalArc extends Geometry implements IGeometry{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The elliptical arc constructor accepts 7 optional arguments that define it's 
	 	* x, y, width, height, start angle, arc and closure type.</p>
	 	* 
	 	* @param x A number indicating the upper left x-axis coordinate.
	 	* @param y A number indicating the upper left y-axis coordinate.
	 	* @param width A number indicating the width.
	 	* @param height A number indicating the height. 
	 	* @param startAngle A number indicating the beginning angle of the arc.
	 	* @param arc A number indicating the the angular extent of the arc, relative to the start angle.
	 	* @param closureType A string indicating the method used to close the arc. 
	 	*/	
		public function EllipticalArc(x:Number=0,y:Number=0,width:Number=0,
		height:Number=0,startAngle:Number=0,arc:Number=0,closureType:String="open"){
			
			super();
			this.x=x;
			this.y=y;
			this.width=width; 
			this.height=height;
			this.startAngle=startAngle;
			this.arc=arc;
			this.closureType=closureType;
			
		}
		
		/**
		* EllipticalArc short hand data value.
		* 
		* <p>The elliptical arc data property expects exactly 6 values x, 
		* y, width, height, startAngle and arc separated by spaces.</p>
		* 
		* @see Geometry#data
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
				invalidated = true;
			}
			
		} 
		
		private var _x:Number=0;
		/**
		* The x-axis coordinate of the upper left point of the arcs enclosure. If not specified 
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
		* The y-axis coordinate of the upper left point of the arcs enclosure. If not specified 
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
		
		
		private var _startAngle:Number=0;
		/**
		* The beginning angle of the arc. If not specified 
		* a default value of 0 is used.
		**/
		public function get startAngle():Number{
			return _startAngle;
		}
		public function set startAngle(value:Number):void{
			if(_startAngle != value){
				_startAngle = value;
				invalidated = true;
			}
		}
		
		
		private var _arc:Number=0;
		/**
		* The angular extent of the arc. If not specified 
		* a default value of 0 is used.
		**/
		public function get arc():Number{
			return _arc;
		}
		public function set arc(value:Number):void{
			if(_arc != value){
				_arc = value;
				invalidated = true;
			}
		}
		
		
		private var _width:Number=0;
		/**
		* The width of the arc.
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
		* The height of the arc.
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
		public function get closureType():String{
			return _closureType;
		}
		public function set closureType(value:String):void{
			if(_closureType != value){
				_closureType = value;
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
			
			if(commandStack.length==0){return;}
			
			var boundsMaxX:int =0;
			var boundsMaxY:int =0;
			var boundsMinX:int =int.MAX_VALUE;
			var boundsMinY:int =int.MAX_VALUE;
			
			//draw each item in the array
			var item:Object;
			for each (item in commandStack){
				with(item){
					switch(type){
						case "m":
							break;
						case "l":
							boundsMinX = Math.min(boundsMinX,x);
							boundsMaxX = Math.max(boundsMaxX,x);
							boundsMinY = Math.min(boundsMinY,y);
							boundsMaxY = Math.max(boundsMaxY,y);
							break;
						case "c":
					
							boundsMinX = Math.min(boundsMinX,x);
							boundsMinX = Math.min(boundsMinX,x1);
							boundsMinX = Math.min(boundsMinX,cx);
							boundsMaxX = Math.max(boundsMaxX,x);
							boundsMaxX = Math.max(boundsMaxX,x1);
							boundsMaxX = Math.max(boundsMaxX,cx);
							  	
							boundsMinY = Math.min(boundsMinY,y);
							boundsMinY = Math.min(boundsMinY,y1);
							boundsMinY = Math.min(boundsMinY,cy);
							boundsMaxY = Math.max(boundsMaxY,y);
							boundsMaxY = Math.max(boundsMaxY,y1);
							boundsMaxY = Math.max(boundsMaxY,cy);
							
							break;
					}
				}				
	  		}
	  		
	      	_bounds = new Rectangle(boundsMinX,boundsMinY,boundsMaxX-boundsMinX,boundsMaxY-boundsMinY);
			
		}	
		
		/**
		* An Array of flash rendering commands that make up this element. 
		**/
		protected var commandStack:Array=[];
		
		/**
		* @inheritDoc 
		**/
		override public function preDraw():void{
			if(invalidated){
				
				//calculate based on startangle, radius, width, and height to get the drawing
				//x and y so that our arc is always in the bounds of the rectangle. may want 
				//to store this local sometime
				var newX:Number = (width/2) + (width/2) * 
				Math.cos(startAngle * (Math.PI / 180))+x;
				
				var newY:Number = (height/2) - (height/2) * 
				Math.sin(startAngle * (Math.PI / 180))+y;
								
				//reset the array
				commandStack =[];
				
				commandStack.push({type:"m",x:x,y:y});
											
				//Calculate the center point. We only needed is we have a pie type 
				//closeur. May want to store this local sometime
				var ax:Number=newX-Math.cos(-(startAngle/180)*Math.PI)*_width/2;
				var ay:Number=newY-Math.sin(-(startAngle/180)*Math.PI)*_height/2;
				
				//draw the start line in the case of a pie type
				if (closureType =="pie"){
					if(Math.abs(arc)<360){
						commandStack.push({type:"m",x:ax,y:ay});
						commandStack.push({type:"l",x:newX,y:newY});
					}
				}
				
				commandStack.push({type:"m",x:newX,y:newY});
				
				//fill the quad array with curve to segments 
				//which we'll use to draw and calc the bounds
				ArcUtils.drawEllipticalArc(newX,newY,_startAngle,_arc,_width/2,_height/2,commandStack);
				
				//close the arc if required
				if(Math.abs(arc)<360){
					if (closureType == "pie"){
						commandStack.push({type:"l",x:ax,y:ay});
					}
					if(closureType == "chord"){
						commandStack.push({type:"l",x:newX,y:newY});
					}
				}
				
				calcBounds();	
				
				invalidated = false;
				
			}
		}
		
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
				with(item){
					switch(type){
						case "m":
							graphics.moveTo(x,y);
							break;
						case "l":
							graphics.lineTo(x,y);
							break;
						case "c":
							graphics.curveTo(cx,cy,x1,y1);
							break;
					}
				}
			}
			
			super.endDraw(graphics);
	  		
		}
		
	}
}