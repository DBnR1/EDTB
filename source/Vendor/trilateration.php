<?php
/**
 * Trilateration function for calculating 3D coordinates
 *
 * $param array $p1
 * $param array $p2
 * $param array $p3
 * $param array $p4
 * @return string
 * @author Snuble and Harbinger (https://forums.frontier.co.uk/showthread.php?t=43362&page=7&p=869662&highlight=trilateration3d#post869662)
 */
function trilateration3d($p1,$p2,$p3,$p4)
{
        $ex = vector_unit(vector_diff($p2, $p1));
        $i = vector_dot_product($ex, vector_diff($p3, $p1));
        $ey = vector_unit(vector_diff(vector_diff($p3, $p1), vector_multiply($ex, $i)));
        $ez = vector_cross($ex,$ey);
        $d = vector_length($p2, $p1);
        $r1 = $p1[3]; $r2 = $p2[3]; $r3 = $p3[3]; $r4 = $p4[3];
        if($d - $r1 >= $r2 || $r2 >= $d + $r1){
                return array();
        }
        $j = vector_dot_product($ey, vector_diff($p3, $p1));
        $x = (($r1*$r1) - ($r2*$r2) + ($d*$d)) / (2*$d);
        $y = ((($r1*$r1) - ($r3*$r3) + ($i*$i) + ($j*$j)) / (2*$j)) - (($i*$x) / $j);
        $z = $r1*$r1 - $x*$x - $y*$y;

        if($z < 0){
                return array();
        }
        $z1 = sqrt($z);
        $z2 = $z1 * -1;

        $result1 = $p1;
        $result1 = vector_sum($result1, vector_multiply($ex, $x));
        $result1 = vector_sum($result1, vector_multiply($ey, $y));
        $result1 = vector_sum($result1, vector_multiply($ez, $z1));
        $result2 = $p1;
        $result2 = vector_sum($result2, vector_multiply($ex, $x));
        $result2 = vector_sum($result2, vector_multiply($ey, $y));
        $result2 = vector_sum($result2, vector_multiply($ez, $z2));

        $r1 = vector_length($p4, $result1);
        $r2 = vector_length($p4, $result2);
        $t1 = $r1 - $r4;
        $t2 = $r2 - $r4;
        $coords = array();

        if(abs($t1) < abs($t2)){

                $result1[0]+=(1/64);
                $result1[0]*=32;
                $result1[0]=floor($result1[0]);
                $result1[0]/=32;

                $result1[1]+=(1/64);
                $result1[1]*=32;
                $result1[1]=floor($result1[1]);
                $result1[1]/=32;

                $result1[2]+=(1/64);
                $result1[2]*=32;
                $result1[2]=floor($result1[2]);
                $result1[2]/=32;

                $coords = array($result1[0], $result1[1], $result1[2]);
        }
        else{

                $result2[0]+=(1/64);
                $result2[0]*=32;
                $result2[0]=floor($result2[0]);
                $result2[0]/=32;

                $result2[1]+=(1/64);
                $result2[1]*=32;
                $result2[1]=floor($result2[1]);
                $result2[1]/=32;

                $result2[2]+=(1/64);
                $result2[2]*=32;
                $result2[2]=floor($result2[2]);
                $result2[2]/=32;

                $coords = array($result2[0], $result2[1], $result2[2]);
        }

        return $coords;
}

function vector_length($p1, $p2){
        $a1 = $p1[0];
        $a2 = $p2[0];
        $b1 = $p1[1];
        $b2 = $p2[1];
        $c1 = $p1[2];
        $c2 = $p2[2];
        $dist = sqrt((($a2-$a1)*($a2-$a1))+(($b2-$b1)*($b2-$b1))+(($c2-$c1)*($c2-$c1)));

        return round($dist, 3, PHP_ROUND_HALF_EVEN);
}

function vector_sum($v1, $v2){
        $v = array();
        $v[0] = $v1[0] + $v2[0];
        $v[1] = $v1[1] + $v2[1];
        $v[2] = $v1[2] + $v2[2];
        return $v;
}

function vector_cross($v1, $v2){
        $v = array();
        $v[0] = ($v1[1] * $v2[2]) - ($v1[2] * $v2[1]);
        $v[1] = ($v1[2] * $v2[0]) - ($v1[0] * $v2[2]);
        $v[2] = ($v1[0] * $v2[1]) - ($v1[1] * $v2[0]);
        return $v;
}

function vector_multiply($v, $i){
        return array($v[0] * $i, $v[1] * $i, $v[2] * $i);
}

function vector_dot_product($v1, $v2){
        $ret = ($v1[0] * $v2[0]) + ($v1[1] * $v2[1]) + ($v1[2] * $v2[2]);
        return $ret;
}

function vector_diff($p1,$p2){
        $ret = array($p1[0] - $p2[0], $p1[1] - $p2[1], $p1[2] - $p2[2]);
        return $ret;
}

function vector_norm($v){
        $l = sqrt(($v[0]*$v[0])+($v[1]*$v[1])+($v[2]*$v[2]));
        return $l;
}

function vector_div($v, $l){
        return array($v[0]/$l, $v[1] / $l, $v[2] / $l);
}

function vector_unit($v){
        $l = vector_norm($v);
        if($l == 0){
                return -1;
        }
        return vector_div($v, $l);
}
