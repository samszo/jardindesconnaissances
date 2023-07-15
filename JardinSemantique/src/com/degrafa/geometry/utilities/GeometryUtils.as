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
package com.degrafa.geometry.utilities{
	
	import com.degrafa.GraphicPoint;
	
	import flash.geom.Rectangle;
	
	/**
	* A helper utility class for various geometric calculations.
	**/
	public class GeometryUtils{
		
		/**
		* Calculates the barycenter of a quadratic bezier curve.
		*  
		* @param a A number indicating the start axis coordinate.
		* @param a A number indicating the control axis coordinate.
		* @param a A number indicating the end axis coordinate.
		* @param t A number indicating the accuracy.
		* 
		* @return The barycenter of the given points.  
		**/
		public static function barycenter(a:Number, b:Number, c:Number, t:Number):Number{ 
			return (1-t)*(1-t)*a + 2*(1-t)*t*b + t*t*c; 
		}
		
		/**
		* Calculates the perimeter of a quadratic bezier curve
		* 
		* @param x A number indicating the starting x-axis coordinate.
	 	* @param y A number indicating the starting y-axis coordinate.
	 	* @param cx A number indicating the control x-axis coordinate. 
	 	* @param cy A number indicating the control y-axis coordinate.
	 	* @param x1 A number indicating the ending x-axis coordinate.
	 	* @param y1 A number indicating the ending y-axis coordinate. 
		* 
		* @return The perimeter distance for the bezier curve.
		**/
		public static function perimeter(x:Number,y:Number,cx:Number,cy:Number,x1:Number,y1:Number):Number{ 
		   
		    var oldX:Number = x; 
		    var oldY:Number = y; 
		    var distance:Number = 0; 
		    var posx:Number;
		    var posy:Number;
		    var dx:Number;
		    var dy:Number;
		    var dist:Number;
		    
		    for(var i:Number=0;i<=1;i+=0.001){ 
		       	posx = barycenter(x,cx,x1,i); 
		       	posy = barycenter(y,cy,y1,i); 
		       	dx = Math.abs(posx - oldX); 
		       	dy = Math.abs(posy - oldY); 
		       	dist = Math.sqrt((dx*dx)+(dy*dy)); 
		       	distance+=dist; 
		       	oldX = posx; 
		    	oldY = posy; 
		    } 
		    
		    return distance; 
		} 
		
		/**
		* Return the tight bounding rectangle for a bezier curve.
		* 
		* @param x A number indicating the starting x-axis coordinate.
	 	* @param y A number indicating the starting y-axis coordinate.
	 	* @param cx A number indicating the control x-axis coordinate. 
	 	* @param cy A number indicating the control y-axis coordinate.
	 	* @param x1 A number indicating the ending x-axis coordinate.
	 	* @param y1 A number indicating the ending y-axis coordinate. 
		* 
		* @return The bounds rectangle for the bezier curve.  
		**/
		public static function bezierBounds(x:Number,y:Number,cx:Number,cy:Number,x1:Number,y1:Number):Rectangle{
			
			
			var t: Number;
			var bounds:Object = {};
			
			//-- yMax
			if( y > y1 ){	
				if( cy > y1 ){ 
					bounds.yMin = y1;
				}
				else{
					t = -( cy - y ) / ( y1 - 2 * cy + y );
					bounds.yMin = ( 1 - t ) * ( 1 - t ) * y + 2 * t * ( 1 - t ) * cy + t * t * y1;
				}
			}
			else{
				if( cy > y ){
					bounds.yMin = y;
				} 
				else{
					t = -( cy - y ) / ( y1 - 2 * cy + y );
					bounds.yMin = ( 1 - t ) * ( 1 - t ) * y + 2 * t * ( 1 - t ) * cy + t * t * y1;
				}
			}
			
			//-- yMin
			if( y > y1 ){	
				if( cy < y ){ 
					bounds.yMax = y;
				}
				else{
					t = -( cy - y ) / ( y1 - 2 * cy + y );
					bounds.yMax = ( 1 - t ) * ( 1 - t ) * y + 2 * t * ( 1 - t ) * cy + t * t * y1;
				}
			}
			else{
				if( y1 > cy ){
					bounds.yMax = y1;
				} 
				else{
					t = -( cy - y ) / ( y1 - 2 * cy + y );
					bounds.yMax = ( 1 - t ) * ( 1 - t ) * y + 2 * t * ( 1 - t ) * cy + t * t * y1;
				}
			}
			
			//-- xMin
			if( x > x1 ){	
				if( cx > x1 ){
					bounds.xMin = x1;
				} 
				else{
					t = -( cx - x ) / ( x1 - 2 * cx + x );
					bounds.xMin = ( 1 - t ) * ( 1 - t ) * x + 2 * t * ( 1 - t ) * cx + t * t * x1;
				}
			}
			else{
				if( cx > x ){ 
					bounds.xMin = x;
				}
				else{
					t = -( cx - x ) / ( x1 - 2 * cx + x );
					bounds.xMin = ( 1 - t ) * ( 1 - t ) * x + 2 * t * ( 1 - t ) * cx + t * t * x1;
				}
			}
		
			//-- xMax
			if( x > x1 ){	
				if( cx < x ){ 
					bounds.xMax = x;
				}
				else{
					t = -( cx - x ) / ( x1 - 2 * cx + x );
					bounds.xMax = ( 1 - t ) * ( 1 - t ) * x + 2 * t * ( 1 - t ) * cx + t * t * x1;
				}
			}
			else{
				if( cx < x1 ){ 
					bounds.xMax = x1;
				}
				else{
					t = -( cx - x ) / ( x1 - 2 * cx + x );
					bounds.xMax = ( 1 - t ) * ( 1 - t ) * x + 2 * t * ( 1 - t ) * cx + t * t * x1;
				}
			}
			
			return new Rectangle(bounds.xMin,bounds.yMin,bounds.xMax-bounds.xMin,bounds.yMax-bounds.yMin);
			
		}	
	
		/**
		* LineIntersects
		* Returns the point of intersection between two lines
		* @param p1, p2 (GraphicPoint) line 1 point struct
		* @param p3, p4 (GraphicPoint) line 2 point struct
		* @return GraphicPoint (Point object of intersection)
		*/
		public static function lineIntersects (p1:GraphicPoint, p2:GraphicPoint, p3:GraphicPoint, p4:GraphicPoint):GraphicPoint {
			var x1:Number = p1.x; 
			var y1:Number = p1.y;
			var x4:Number = p4.x; 
			var y4:Number = p4.y;
		    var dx1:Number = p2.x - x1;
		    var dx2:Number = p3.x - x4;
	
			var intersectPoint:GraphicPoint = new GraphicPoint()
		
			if (!(dx1 || dx2)){
				
				intersectPoint.x=0;
				intersectPoint.y=0;
			
			 	//return NaN;
			}
			
			var m1:Number = (p2.y - y1) / dx1;
			var m2:Number = (p3.y - y4) / dx2;
			
			if (!dx1){
				intersectPoint.x=x1;
				intersectPoint.y=m2 * (x1 - x4) + y4;
				return intersectPoint;
			} 
			else if (!dx2){
				intersectPoint.x=x4;
				intersectPoint.y=m1 * (x4 - x1) + y1;
				return intersectPoint;
			}
			
			var xInt:Number = (-m2 * x4 + y4 + m1 * x1 - y1) / (m1 - m2);
	   		var yInt:Number = m1 * (xInt - x1) + y1;
	   			
			intersectPoint.x=xInt;
			intersectPoint.y=yInt;
			
			return intersectPoint;
			
		}
		
		/**
		* MidPoint
		* Returns the midpoint Point of 2 Point structures
		* @param p1 GraphicPoint Struc 1
		* @param p2 GraphicPoint Struc 2
		* @return GraphicPoint (the midpoint of the 2 points)
		*/
		public static function midPoint(p1:GraphicPoint, p2:GraphicPoint):GraphicPoint{
			return new GraphicPoint((p1.x + p2.x)/2,(p1.y + p2.y)/2);
		}
		
		/**
		* SplitBezier
		* Divides a cubic bezier curve into two cubic bezier curve definitions
		* 
		* @param p1 (GraphicPoint) endpoint 1
		* @param c1 (GraphicPoint) control point 1
		* @param c2 (GraphicPoint)control point 2
		* @param p2 (GraphicPoint) endpoint 2
		* @return Object (object with two cubic bezier definitions, b0 and b1) 
		*/
		public static function splitBezier(p1:GraphicPoint, c1:GraphicPoint, c2:GraphicPoint, p2:GraphicPoint):Object{	    						
		   
		    var p01:GraphicPoint = midPoint(p1, c1);
		    var p12:GraphicPoint = midPoint(c1, c2);
		    var p23:GraphicPoint = midPoint(c2, p2);
		    var p02:GraphicPoint = midPoint(p01, p12);
		    var p13:GraphicPoint = midPoint(p12, p23);
		    var p03:GraphicPoint = midPoint(p02, p13);
						
			return { b0:{p1:p1, c1:p01, c2:p02, p2:p03}, b1:{p1:p03, c1:p13, c2:p23, p2:p2} };
			
		}
	
		/**
		* Round a number a specified number of decimal places.
		* 
		* @param input The number to round.
		* @param input The number of deciaml points to round to.
		* 
		* @return The resulting rounded number.     
   		**/ 
		public static function roundTo(input:Number, digits:Number):Number{
			return Math.round(input*Math.pow(10, digits))/Math.pow(10, digits);
		}

		
		/**
		* Convert Degress to radius.
		* 
		* @param angle A angle value to convert.
		* 
		* @return The resulting number converted to a radius.     
   		**/  
		public static function degressToRadius(angle:Number):Number{
			return angle*(Math.PI/180);
		}
		
		/**
		* Convert radius to degrees.
		* 
		* @param angle A angle radius to convert.
		* 
		* @return The resulting number converted to degress.     
   		**/
		public static function radiusToDegress(angle:Number):Number{
			return angle*(180/Math.PI);
		}

		/**
		* Rotate a point by a degrees.
		* 
		* @param point The point to rotate.
		* @param degrees A radius to rotate.
		* 
		* @return The transformed GraphicPoint point object.     
   		**/  
		public static function rotatePoint(value:GraphicPoint,angle:Number):GraphicPoint{
			var radius:Number = Math.sqrt(Math.pow(value.x, 2)+Math.pow(value.y, 2));
			var angle:Number = Math.atan2(value.y, value.x)+degressToRadius(angle);
			return new GraphicPoint(roundTo(radius*Math.cos(angle), 3), roundTo(radius*Math.sin(angle), 3));
		}
		
		/**
		* Rotate a point around a given center point by degrees.
		* 
		* @param point The point to rotate.
		* @param centerPoint the center point that point should be roatated around.
		* @param degrees A radius to rotate.
		* 
		* @return The transformed GraphicPoint point object.     
   		**/  
		public static function rotatePointOnCenterPoint(point:GraphicPoint,centerPoint:GraphicPoint,degrees:Number):GraphicPoint{
			var tempReturnPoint:GraphicPoint = new GraphicPoint();
			var radians:Number = (degrees/180)*Math.PI;
			
			tempReturnPoint.x = centerPoint.x + ( Math.cos(radians) * 
			(point.x - centerPoint.x) - Math.sin(radians) * 
			(point.y - centerPoint.y));
			
		    tempReturnPoint.y = centerPoint.y + ( Math.sin(radians) * 
		    (point.x - centerPoint.x) + Math.cos(radians) * 
		    (point.y - centerPoint.y) )
							
			return tempReturnPoint;
			
		}
		
	
		/**
		* CubicToQuadratic
		* <p>Approximates a cubic bezier with as many quadratic bezier segments (n) as required 
		* to achieve a specified tolerance.</p>
		* 
		* @param p1 (GraphicPoint) endpoint
		* @param c1 (GraphicPoint) 1st control point
		* @param c2 (GraphicPoint) 2nd control point
		* @param p2 (GraphicPoint) endpoint
		* @param k: tolerance (low number = most accurate result)
		* @param qcurves (Array) will contain array of quadratic bezier curves, each element containing p1x, p1y, cx, cy, p2x, p2y (start point, control points, end point
		*/
	 	public static function cubicToQuadratic(p1:GraphicPoint, c1:GraphicPoint, c2:GraphicPoint, p2:GraphicPoint, k:Number, quadratics:Array):void{
	 			 		
			// find intersection between bezier arms
			var s:Object = lineIntersects(p1, c1, c2, p2);
			
			// find distance between the midpoints
			var dx:Number = (p1.x + p2.x + s.x * 4 - (c1.x + c2.x) * 3) * .125;
			var dy:Number = (p1.y + p2.y + s.y * 4 - (c1.y + c2.y) * 3) * .125;
			
			// split curve if the quadratic isn't close enough
			if (dx*dx + dy*dy > k){
				var halves:Object = splitBezier(p1, c1, c2, p2);
				var b0:Object = halves.b0; 
				var b1:Object = halves.b1;
				// recursive call to subdivide curve
				cubicToQuadratic (p1,b0.c1, b0.c2, b0.p2,k, quadratics);
				cubicToQuadratic (b1.p1,b1.c1,b1.c2, p2,k, quadratics);
			} 
			else{
				// end recursion by saving points
				quadratics.push({p1x:p1.x, p1y:p1.y, cx:s.x, cy:s.y, p2x:p2.x, p2y:p2.y});
			}
		}
	
	}
}