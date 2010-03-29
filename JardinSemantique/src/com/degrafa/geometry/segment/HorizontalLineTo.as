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
	
	import flash.geom.Point;
	import flash.geom.Rectangle;
	
	//(H or h) path data command
	[Bindable]	
	/**
 	*  A horizontal line (H,h) segment is defined by a ending x-axis point.
 	*  
 	*  @see http://www.w3.org/TR/SVG/paths.html#PathDataLinetoCommands
 	*  
 	**/
	public class HorizontalLineTo extends Segment implements ISegment{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The HorizontalLineTo constructor accepts 2 optional arguments that define it's 
	 	* data and a coordinate type.</p>
	 	* 
	 	* @param data A string indicating the data to be used for this segment.
	 	* @param coordinateType A string indicating the coordinate type to be used for this segment.
	 	**/
		public function HorizontalLineTo(data:String=null,coordinateType:String="absolute"):void{
			this.data =data;
			this.coordinateType=coordinateType;
			this.isShortSequence = false;		
		}
		
		/**
		* Return the segment type
		**/
		override public function get segmentType():String{
			return "HorizontalLineTo";
		}
		
		/**
		* HorizontalLineTo short hand data value.
		* 
		* <p>The horizontal line to data property expects exactly 1 value 
		* x.</p>
		* 
		* @see Segment#data
		* 
		**/
		override public function set data(value:String):void{
			if(super.data != value){
				super.data = value;
				_x=Number(value);
				invalidated = true;
			}
		}  		
						
		private var _x:Number=0;
		/**
		* The x-coordinate of the end point of the line. If not specified 
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
		
		
		/**
		* Calculates the bounds for this segment. 
		**/	
		private function calcBounds():void{
			_bounds = new Rectangle(Math.min(lastPoint.x,absRelOffset.x+x),
			Math.min(lastPoint.y,lastPoint.y), absRelOffset.x+x-lastPoint.x,
			lastPoint.y-lastPoint.y);
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
		
		/**
		* Compute the segment using x and y as the start point adding it's commands to
		* the drawing stack 
		**/
		public function computeSegment(lastPoint:Point,absRelOffset:Point,commandStack:Array):void{
			
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
			
			var item:Object;
			
			if(!invalidated){
				for each(item in this.commandStack){
					commandStack.push(item);		
				}
			}
			
			//reset the array
			this.commandStack=[];
						
			this.commandStack.push({type:"l", x:absRelOffset.x+x,y:lastPoint.y});
        	
        	//create a return command array adding each item from the local array
			for each(item in this.commandStack){
				commandStack.push(item);
			}
        	
			this.lastPoint =lastPoint;
			this.absRelOffset=absRelOffset;
			
			//pre calculate the bounds for this segment
			preDraw();
			
			//old just for refference tilltesting complete
        	//calcBounds(lastPoint,new Point(absRelOffset.x+x,lastPoint.y));
			
		}
		
		
	}
}