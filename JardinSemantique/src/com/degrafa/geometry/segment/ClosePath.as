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
	
	//close path to the last used move
	//(Z or z) path data command
	[Exclude(name="data", kind="property")]
	[Exclude(name="coordinateType", kind="property")]
	[Exclude(name="isShortSequence", kind="property")]
	/**
	* The "closepath" (Z or z) ends the current subpath
	* 
	* @see http://www.w3.org/TR/SVG/paths.html#PathDataClosePathCommand 
	* 
	**/
	public class ClosePath extends Segment implements ISegment{
		public function ClosePath():void{}
		
		/**
		* Setting data on ClosePath has no effect
		**/
		override public function set data(value:String):void{}
		override public function get data():String{return null;}
		
		/**
		* Setting coordinateType on ClosePath has no effect
		**/
		override public function set coordinateType(value:String):void{}
		override public function get coordinateType():String{return null;}
		
		/**
		* Setting isShortSequence on ClosePath has no effect
		**/
		override public function get isShortSequence():Boolean{return false;};
		override public function set isShortSequence(value:Boolean):void{};
		
		/**
		* Return the segment type
		**/
		override public function get segmentType():String{
			return "ClosePath";
		}
		
		/**
		* Calculates the bounds for this segment. 
		**/	
		private function calcBounds():void{
			_bounds = new Rectangle(Math.min(lastPoint.x,firstPoint.x),
			Math.min(lastPoint.y,firstPoint.y), firstPoint.x-lastPoint.x,
			firstPoint.y-lastPoint.y);
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
		private var firstPoint:Point;
		
		/**
		* Compute the segment using x and y as the start point adding it's commands to
		* the drawing stack 
		**/
		public function computeSegment(lastPoint:Point,firstPoint:Point,commandStack:Array):void{
			
			if(!invalidated && lastPoint){
				if(this.lastPoint && !invalidated){
					if(!lastPoint.equals(this.lastPoint)){
						invalidated =true;
					}
				}
			}
			
			if(!invalidated && firstPoint){
				if(this.firstPoint && !invalidated){
					if(!firstPoint.equals(this.firstPoint)){
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
			
			this.commandStack.push({type:"l", x:firstPoint.x,y:firstPoint.y});
			
			//create a return command array adding each item from the local array
			for each(item in this.commandStack){
				commandStack.push(item);
			}
			
			this.lastPoint =lastPoint;
			this.firstPoint=firstPoint;
			
			//pre calculate the bounds for this segment
			preDraw();
			
		}
	}
}