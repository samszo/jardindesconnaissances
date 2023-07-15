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
package com.degrafa{
	
	/**
	* servers as a proxy wrapper so we can get the event manager in here. 
	* This should/can not be used in a matrix where flex/flash expect a 
	* point. But rather use/pass/set the point property when required. It should 
	* never evenr be used on a complex graphic. It is intended to be wrapped for 
	* a current object if manipulation is required.
	**/ 
	
	import com.degrafa.core.DegrafaObject;
	import flash.geom.Point;
	import mx.events.PropertyChangeEvent;
	
	
	[Bindable(event="propertyChange")]
	/**
	* Extended Degrafa event enabled point definition.
	* 
	* @see flash.geom.Point   
	**/
	public class GraphicPointEX extends DegrafaObject implements IGraphicPoint{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The graphic extended point constructor accepts 2 optional arguments that define it's 
	 	* x, and y coordinate values.</p>
	 	* 
	 	* @param x A number indicating the x-axis coordinate.
	 	* @param y A number indicating the y-axis coordinate.
	 	* 
	 	*/	
		public function GraphicPointEX(x:Number=0, y:Number=0){
			this.x=x
			this.y=y
			this.point.x=x
			this.point.y=y
		}
		
		private var _data:String;
		/**
		* GraphicPointEX short hand data value.
		* 
		* <p>The extended graphic point data property expects exactly 2 values x and 
		* y separated by spaces.</p>
		**/
		public function get data():String{
			return _data;
		}	
		public function set data(value:String):void
		{
			if(_data != value){
				var oldValue:String=_data;
				
				_data = value;
			
				var tempArray:Array = value.split(" ");
					
				if (tempArray.length == 2)
				{
					_x=tempArray[0];
					_y=tempArray[1];
				}
				
				_point.x=_x
				_point.y=_y
				
				//call local helper to dispatch event
				if(enableEvents){	
					initChange("data",oldValue,value,this);
				}
			}
			
		}
		
		private var _point:Point=new Point();
		/**
		* The internal point object.
		**/
		public function get point():Point{
			return _point;
		}
		public function set point(value:Point):void{
			if(!_point.equals(value)){
				
				var oldValue:Point=_point;
				_point = value;
				
				_x=_point.x;
				_y=_point.y;
				
				//call local helper to dispatch event	
				if(enableEvents){	
					initChange("data",oldValue,value,this);
				}
			}
			
		}
		
		
		private var _x:Number=0;
		/**
		* The x-coordinate of the point. If not specified 
		* a default value of 0 is used.
		**/
		public function get x():Number{
			return _x;
		}
		public function set x(value:Number):void{
			if(_x != value){
				var oldValue:Number=_x;
			
				_x = value;
				_point.y=_x;
				
				//call local helper to dispatch event	
				if(enableEvents){	
					initChange("x",oldValue,_x,this);
				}
			}
		}
		
		
		private var _y:Number=0;
		/**
		* The y-coordinate of the point. If not specified 
		* a default value of 0 is used.
		**/
		public function get y():Number{
			return _y;
		}
		public function set y(value:Number):void{
			if(_y != value){
				var oldValue:Number=_y;
			
				_y = value;
				_point.y=_y;
				
				//call local helper to dispatch event	
				if(enableEvents){	
					initChange("y",oldValue,_y,this);
				}
			}
		}
				
		/**
		* Adds the coordinates of another point to the coordinates of this point to create a new point.
		* 
		* @see flash.geom.Point
		**/
		public function add(v:Point):Point{
			return _point.add(v);
		}
		
		/**
		* Creates a copy of this Point object.
		* 
		* @see flash.geom.Point
		**/
		public function clone():Point {
			return _point.clone();
		}
		
		/**
		* Returns the distance between pt1 and pt2.
		* 
		* @see flash.geom.Point
		**/
		public static function distance(pt1:Point, pt2:Point):Number{
			return Point.distance(pt1,pt2);
		} 
		
		/**
		* Determines whether two points are equal.
		* 
		* @see flash.geom.Point
		**/
		public function equals(toCompare:Point):Boolean {
			return _point.equals(toCompare); 
		}
		
		/**
		* Determines a point between two specified points.
		* 
		* @see flash.geom.Point
		**/
		public static function interpolate(pt1:Point, pt2:Point, f:Number):Point{
			return Point.interpolate(pt1,pt2,f); 
		}
		
		/**
		* Scales the line segment between (0,0) and the current point to a set length.
		* 
		* @see flash.geom.Point
		**/
		public function normalize(thickness:Number):void {
			_point.normalize(thickness);
		}
		
		/**
		* Offsets the Point object by the specified amount.
		* 
		* @see flash.geom.Point
		**/
		public function offset(dx:Number, dy:Number):void {
			_point.offset(dx,dy);
		}
		
		/**
		* Converts a pair of polar coordinates to a Cartesian point coordinate.
		* 
		* @see flash.geom.Point
		**/
		public static function polar(len:Number, angle:Number):Point{
			return Point.polar(len,angle); 
		}
		
		/**
		* Subtracts the coordinates of another point from the coordinates of this point to create a new point.
		* 
		* @see flash.geom.Point
		**/
		public function subtract(v:Point):Point{
			return _point.subtract(v); 
		}
		
		/**
		* The length from (0,0) to this point.
		* 
		* @see flash.geom.Point
		**/
		public function get length():Number{
			return _point.length;
		}
		
	}
}