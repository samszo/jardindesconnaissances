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
package com.degrafa.geometry.segment{
	
	import com.degrafa.geometry.utilities.GeometryUtils;
	
	import flash.geom.Point;
	import flash.geom.Rectangle;
	
	//(Q,q,T,t) path data commands
	[Bindable]	
	/**
 	*  Defines a quadratic Bézier curve from the current point to 
 	*  (x,y) using (cx,cy) as the control point.
 	*  
 	*  @see http://www.w3.org/TR/SVG/paths.html#PathDataQuadraticBezierCommands
 	*  
 	**/
	public class QuadraticBezierTo extends Segment implements ISegment{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The QuadraticBezierTo constructor accepts 3 optional arguments that define it's 
	 	* data, coordinate type and a flag that specifies a short sequence.</p>
	 	* 
	 	* @param data A string indicating the data to be used for this segment.
	 	* @param coordinateType A string indicating the coordinate type to be used for this segment.
	 	* @param isShortSequence A boolean indicating the if this segment is a short segment definition. 
	 	**/
		public function QuadraticBezierTo(data:String=null,coordinateType:String="absolute",isShortSequence:Boolean=false):void{
			this.data =data;
			this.coordinateType=coordinateType;
			this.isShortSequence =isShortSequence
		}
		
		/**
		* Return the segment type
		**/		
		override public function get segmentType():String{
			return "QuadraticBezierTo";
		}
				
		/**
		* CubicBezierTo short hand data value.
		* 
		* <p>The cubic Bézier data property expects exactly 4 values 
		* cx, cy, x and y separated by spaces.</p>
		* 
		* @see Segment#data
		* 
		**/
		override public function set data(value:String):void{
			
			if(super.data != value){
				super.data = value;
			
				//parse the string on the space
				var tempArray:Array = value.split(" ");
				
				if (tempArray.length == 4)
				{
					_cx=tempArray[0];
					_cy=tempArray[1];
					_x=tempArray[2];
					_y=tempArray[3];
				}
				invalidated = true;
			}
		}  
				
		private var _x:Number=0;
		/**
		* The x-coordinate of the end point of the curve. If not specified 
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
		* The y-coordinate of the end point of the curve. If not specified 
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
		
				
		private var _cx:Number=0;
		/**
		* The x-coordinate of the control point of the curve. If not specified 
		* a default value of 0 is used.
		**/
		public function get cx():Number{
			return _cx;
		}
		public function set cx(value:Number):void{
			if(_cx != value){
				_cx = value;
				invalidated = true;
			}
		}
		
		
		private var _cy:Number=0;
		/**
		* The y-coordinate of the control point of the curve. If not specified 
		* a default value of 0 is used.
		**/
		public function get cy():Number{
			return _cy;
		}
		public function set cy(value:Number):void{
			if(_cy != value){
				_cy = value;
				invalidated = true;
			}
		}
		
		
		/**
		* Calculates the bounds for this segment. 
		**/	
		private function calcBounds():void{
			
			if(isShortSequence){
				_bounds = GeometryUtils.bezierBounds(lastPoint.x,lastPoint.y,lastPoint.x+(lastPoint.x-lastControlPoint.x),
				lastPoint.y+(lastPoint.y-lastControlPoint.y),absRelOffset.x+x,absRelOffset.y+y);
			}
			else{
				_bounds = GeometryUtils.bezierBounds(lastPoint.x,lastPoint.y,absRelOffset.x+cx,
				absRelOffset.y+cy,absRelOffset.x+x,absRelOffset.y+y);
			}
		}
		
		private var _bounds:Rectangle;
		/**
		* The tight bounds of this segment as represented by a Rectangle object. 
		**/
		public function get bounds():Rectangle{
			return _bounds;	
		}
		
		/**
		* @inheritDoc 
		**/		
		override public function preDraw():void{
			calcBounds();
			invalidated = false;
		} 
		
		/**
		* An Array of flash rendering commands that make up this element. 
		**/
		protected var commandStack:Array=[];
		
		private var lastPoint:Point;
		private var absRelOffset:Point;
		private var lastControlPoint:Point;
		
		/**
		* Compute the segment using x and y as the start point adding it's commands to
		* the drawing stack 
		**/
		public function computeSegment(lastPoint:Point,absRelOffset:Point,lastControlPoint:Point,commandStack:Array):void{
			
			if(!invalidated && lastPoint){
				if(this.lastPoint && !invalidated){
					if(!lastPoint.equals(this.lastPoint)){
						invalidated =true;
					}
				}
			}
			
			if(!invalidated && absRelOffset){
				if(this.absRelOffset && !invalidated){
					if(!absRelOffset.equals(this.absRelOffset)){
						invalidated =true;
					}
				}
			}
			
			if(!invalidated && lastControlPoint){
				if(this.lastControlPoint && !invalidated){
					if(!lastControlPoint.equals(this.lastControlPoint)){
						invalidated =true;
					}
				}
			}
			
			var item:Object;
			
			if(!invalidated){
				for each(item in this.commandStack){
					commandStack.push(item);		
				}
			}
			
			//reset the array
			this.commandStack=[];
									
			if(isShortSequence){
				this.commandStack.push({type:"c", cx:lastPoint.x+(lastPoint.x-lastControlPoint.x),
				cy:lastPoint.y+(lastPoint.y-lastControlPoint.y),
				x1:absRelOffset.x+x,
				y1:absRelOffset.y+y});
			}
			else{
   				this.commandStack.push({type:"c", cx:absRelOffset.x+cx,
				cy:absRelOffset.y+cy,
				x1:absRelOffset.x+x,
				y1:absRelOffset.y+y});
			}
			
			//create a return command array adding each item from the local array
			for each(item in this.commandStack){
				commandStack.push(item);
			}
        	
			this.lastPoint =lastPoint;
			this.absRelOffset=absRelOffset;
			this.lastControlPoint=lastControlPoint;
			
			//pre calculate the bounds for this segment
			preDraw();
							
		
		}
		
		
	}
}