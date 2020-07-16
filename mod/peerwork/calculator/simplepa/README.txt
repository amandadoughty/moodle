
Step 1: calculating the rating given by Student A to Student B

	Each group member is invited to rate the other members of the group under three headings.

	There are 4 possible levels of participation under each heading, which are rated as:
	5 Full participation
	4 Participated pretty well, but lacked something
	2 Some participation, but not a valuable group member
	0 Did nothing

	The raw rating given by student A to student B is the simple average of these three numbers (although it would be possible to use unequal weightings if desired)

	Student A's rating for Student B is the simple average of these three numbers

	Rating(A,B) = RawRating(A,B)

Step 2: combining all the ratings given to student B to get an overall rating for student B
	Take the simple average of the ratings awarded by the other members of the group

	Rating(B) = ScoreSum(B)/ScoreCount(B)

Step 3: calculating the adjustment to the marks for student B
	In each case the adjustment to the final mark for student B is calculated by comparing student B's overall rating with the average of the overall ratings of all members of the group.

	Adjustment(B) = MultiplicativeFactor * (Rating(B) - OverallAverageRating)

	If truncation is to be used, we look to see whether the adjustment lies within the truncation zone (currently from -2.0 to +2.0). If so, the adjustment is set equal to 0.
	
	If Adjustment(B) < UpperZoneBoundary and Adjustment(B) > LowerZoneBoundary then Adjustment(B) = 0

	The adjustment is added to the mark allocated by the marker to the piece of group work.