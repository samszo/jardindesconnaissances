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
	
	import com.degrafa.geometry.utilities.ArcUtils;
	
	import flash.geom.Point;
	import flash.geom.Rectangle;
	
	//(A or a) path data command
	[Bindable]	
	
	/**
	* Defines an elliptical arc (A,a) segment from the current point.
	*  
	* @see http://www.w3.org/TR/SVG/paths.html#PathDataEllipticalArcCommands 
	**/
	public class EllipticalArcTo extends Segment implements ISegment{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The EllipticalArcTo constructor accepts 2 optional arguments that define it's 
	 	* data and a coordinate type.</p>
	 	* 
	 	* @param data A string indicating the data to be used for this segment.
	 	* @param coordinateType A string indicating the coordinate type to be used for this segment.
	 	**/
		public function EllipticalArcTo(data:String=null,coordinateType:String="absolute"):void{
			this.data =data;
			this.coordinateType=coordinateType;
			this.isShortSequence = false;
		}
		
		/**
		* Return the segment type
		**/
		override public function get segmentType():String{
			return "EllipticalArcTo";
		}
		
		
		/**
		* EllipticalArcTo short hand data value.
		* 
		* <p>The elliptical arc to data property expects exactly 7 values 
		* rx, ry, xAxisRotation, largeArcFlag, sweepFlag, x and y separated 
		* by spaces.</p>
		* 
		* @see Segment#data
		* 
		**/
		override public function set data(value:String):void{
			if(super.data != value){
				super.data = value;
				
				//parse the string on the space
				var tempArray:Array = value.split(" ");
				
				if (tempArray.length == 7)
				{
					_rx=tempArray[0]; //ry
					_ry=tempArray[1]; //rx
					_xAxisRotation=tempArray[2]; //x-axis-rotation  
					_largeArcFlag=tempArray[3]; //largeArcFlag
					_sweepFlag=tempArray[4]; //sweepFlag
					_x=tempArray[5]; //x end point
					_y=tempArray[6]; //y end point
					
				}
				invalidated = true;
			}
		}
												
		private var _rx:Number=0;
		/**
		* The x-coordinate radius of the arc. If not specified 
		* a default value of 0 is used.
		**/
		public function get rx():Number{
			return _rx;
		}
		public function set rx(value:Number):void{
			if(_rx != value){
				_rx = value;
				invalidated = true;
			}
		}
		
		
		private var _ry:Number=0;
		/**
		* The y-coordinate radius of the arc. If not specified 
		* a default value of 0 is used.
		**/
		public function get ry():Number{
			return _ry;
		}
		public function set ry(value:Number):void{			
			if(_ry != value){
				_ry = value;
				invalidated = true;
			}
			
		}
		
		
		private var _xAxisRotation:Number=0;
		/**
		* The x axis rotation of the arc. If not specified 
		* a default value of 0 is used.
		**/
		public function get xAxisRotation():Number{
			return _xAxisRotation;
		}
		public function set xAxisRotation(value:Number):void{	
			if(_xAxisRotation != value){
				_xAxisRotation = value;
				invalidated = true;
			}
			
		}
		
		
		
		private var _largeArcFlag:Number=-1;
		/**
		* A value indicating if the arc is to use a large arc. 
		* A value of 0 = true and a value of 1 = false
		*
		* see@ http://www.w3.org/TR/SVG/paths.html#PathDataEllipticalArcCommands  
		**/
		public function get largeArcFlag():Number{
			return _largeArcFlag;
		}
		public function set largeArcFlag(value:Number):void{
			if(_largeArcFlag != value){
				_largeArcFlag = value;
				invalidated = true;
			}
		}
		
		
		private var _sweepFlag:Number=-1;
		/**
		* A value indicating if the arc is to use a sweep. 
		* A value of 0 = true and a value of 1 = false
		*
		* see@ http://www.w3.org/TR/SVG/paths.html#PathDataEllipticalArcCommands  
		**/
		public function get sweepFlag():Number{
			return _sweepFlag;
		}
		public function set sweepFlag(value:Number):void{
			if(_sweepFlag != value){
				_sweepFlag = value;
				invalidated = true;
			}
		}
		
		private var _x:Number=0;
		/**
		* The x-coordinate of the end point of the arc. If not specified 
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
		* The y-coordinate of the end point of the arc. If not specified 
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
		
		
		private var _bounds:Rectangle;
		/**
		* The tight bounds of this segment as represented by a Rectangle object. 
		**/
		public function get bounds():Rectangle{
			return _bounds;	
		}
		
		/**
		* Calculates the bounds for this segment. 
		**/	
		private function calcBounds():void{
			
			if(commandStack.length==0){return;}
			
			var boundsMaxX:int =0;
			var boundsMaxY:int =0;
			var boundsMinX:int =int.MAX_VALUE;
			var boundsMinY:int =int.MAX_VALUE;
			
			for each(var item:Object in this.commandStack){
				with(item){
					if(type=="m"){
						boundsMinX = Math.min(boundsMinX,x);
						boundsMinY = Math.min(boundsMinY,y);
						boundsMaxX = Math.max(boundsMaxX,x);
						boundsMaxY = Math.max(boundsMaxY,y);
					}
					else{
						boundsMinX = Math.min(boundsMinX,x1);
						boundsMinX = Math.min(boundsMinX,cx);
						boundsMaxX = Math.max(boundsMaxX,x1);
						boundsMaxX = Math.max(boundsMaxX,cx);
					
						boundsMinY = Math.min(boundsMinY,y1);
						boundsMinY = Math.min(boundsMinY,cy);
						boundsMaxY = Math.max(boundsMaxY,y1);
						boundsMaxY = Math.max(boundsMaxY,cy);
					}
				}
				
			}
	  		
	  		//update with the points passed
	  		if(lastPoint){
	  			boundsMaxX = Math.max(boundsMaxX,lastPoint.x);
	  			boundsMinX = Math.min(boundsMinX,lastPoint.x);
	  			boundsMaxY = Math.max(boundsMaxY,lastPoint.y);
	  			boundsMinY = Math.min(boundsMinY,lastPoint.y);
	  		}
	  			  		
	      	_bounds = new Rectangle(boundsMinX,boundsMinY,boundsMaxX-boundsMinX,boundsMaxY-boundsMinY);
			
		}	
		
		/**
		* @inheritDoc 
		**/
		override public function preDraw():void{
			
			calcBounds();
			invalidated = false;
			
		} 
		
		private var lastPoint:Point;
		private var absRelOffset:Point;
		
		/**
		* An Array of flash rendering commands that make up this element. 
		**/
		protected var commandStack:Array=[];
		
		/**
		* Compute the segment using x and y as the start point adding it's commands to
		* the drawing stack 
		**/
		public function computeSegment(lastPoint:Point,absRelOffset:Point,commandStack:Array):void{
			
			var item:Object
			
			//test if anything has changed and only recalculate if something has
			if(!invalidated){
				for each(item in this.commandStack){
					commandStack.push(item);
				}
				return;
			}
			
			//reset the array
			this.commandStack=[];
			
			var computedArc:Object = ArcUtils.computeSvgArc(rx,ry,xAxisRotation,Boolean(largeArcFlag),
			Boolean(sweepFlag),x+absRelOffset.x,y+absRelOffset.y,lastPoint.x,lastPoint.y);
	        
	        ArcUtils.drawArc(computedArc.x,computedArc.y,computedArc.startAngle,
	        computedArc.arc,computedArc.radius,computedArc.yRadius,
	        computedArc.xAxisRotation,this.commandStack);
			
			//add the move to at the start of the stack
			this.commandStack.unshift({type:"m", x:computedArc.x,y:computedArc.y});
						
			//create a return command array adding each item from the local array
			for each(item in this.commandStack){
				commandStack.push(item);
			}
			
			this.lastPoint = lastPoint;
			this.absRelOffset = absRelOffset;
			
			//pre calculate the bounds for this segment
			preDraw();
					
		}
			
	}
}