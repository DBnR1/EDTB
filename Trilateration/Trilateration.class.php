<?php
/**
 * Class to generate coordinates based on reference systems
 *
 * No description
 *
 * @package EDTB\Backend
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

 /*
 * ED ToolBox, a companion web app for the video game Elite Dangerous
 * (C) 1984 - 2016 Frontier Developments Plc.
 * ED ToolBox or its creator are not affiliated with Frontier Developments Plc.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 */

/**
 * Class Trilateration
 */
class Trilateration
{
    /**
     * vector_length function for trilateration
     *
     * @param array $p1
     * @param array $p2
     * @return float
     * @author Snuble and Harbinger (https://forums.frontier.co.uk/showthread.php?t=43362&page=7&p=869662&highlight=trilateration3d#post869662)
     */
    private function vector_length($p1, $p2)
    {
        $a1 = $p1[0];
        $a2 = $p2[0];
        $b1 = $p1[1];
        $b2 = $p2[1];
        $c1 = $p1[2];
        $c2 = $p2[2];
        $dist = sqrt((($a2-$a1)*($a2-$a1))+(($b2-$b1)*($b2-$b1))+(($c2-$c1)*($c2-$c1)));

        return round($dist, 3, PHP_ROUND_HALF_EVEN);
    }

    /**
     * vector_sum function for trilateration
     *
     * @param array $v1
     * @param array $v2
     * @return float
     * @author Snuble and Harbinger (https://forums.frontier.co.uk/showthread.php?t=43362&page=7&p=869662&highlight=trilateration3d#post869662)
     */
    private function vector_sum($v1, $v2)
    {
        $v = [];
        $v[0] = $v1[0] + $v2[0];
        $v[1] = $v1[1] + $v2[1];
        $v[2] = $v1[2] + $v2[2];
        return $v;
    }

    /**
     * vector_cross function for trilateration
     *
     * @param array $v1
     * @param array $v2
     * @return float
     * @author Snuble and Harbinger (https://forums.frontier.co.uk/showthread.php?t=43362&page=7&p=869662&highlight=trilateration3d#post869662)
     */
    private function vector_cross($v1, $v2)
    {
        $v = [];
        $v[0] = ($v1[1] * $v2[2]) - ($v1[2] * $v2[1]);
        $v[1] = ($v1[2] * $v2[0]) - ($v1[0] * $v2[2]);
        $v[2] = ($v1[0] * $v2[1]) - ($v1[1] * $v2[0]);
        return $v;
    }

    /**
     * vector_multiply function for trilateration
     *
     * @param array $v
     * @param array $i
     * @return array
     * @author Snuble and Harbinger (https://forums.frontier.co.uk/showthread.php?t=43362&page=7&p=869662&highlight=trilateration3d#post869662)
     */
    private function vector_multiply($v, $i)
    {
        return array($v[0] * $i, $v[1] * $i, $v[2] * $i);
    }

    /**
     * vector_dot_product function for trilateration
     *
     * @param array $v1
     * @param array $v2
     * @return float
     * @author Snuble and Harbinger (https://forums.frontier.co.uk/showthread.php?t=43362&page=7&p=869662&highlight=trilateration3d#post869662)
     */
    private function vector_dot_product($v1, $v2)
    {
        $ret = ($v1[0] * $v2[0]) + ($v1[1] * $v2[1]) + ($v1[2] * $v2[2]);
        return $ret;
    }

    /**
     * vector_diff function for trilateration
     *
     * @param array $p1
     * @param array $p2
     * @return float
     * @author Snuble and Harbinger (https://forums.frontier.co.uk/showthread.php?t=43362&page=7&p=869662&highlight=trilateration3d#post869662)
     */
    private function vector_diff($p1, $p2)
    {
        $ret = array($p1[0] - $p2[0], $p1[1] - $p2[1], $p1[2] - $p2[2]);
        return $ret;
    }

    /**
     * vector_norm function for trilateration
     *
     * @param array $v
     * @return float
     * @author Snuble and Harbinger (https://forums.frontier.co.uk/showthread.php?t=43362&page=7&p=869662&highlight=trilateration3d#post869662)
     */
    private function vector_norm($v)
    {
        $l = sqrt(($v[0]*$v[0])+($v[1]*$v[1])+($v[2]*$v[2]));
        return $l;
    }

    /**
     * vector_div function for trilateration
     *
     * @param array $v
     * @param float $l
     * @return array
     * @author Snuble and Harbinger (https://forums.frontier.co.uk/showthread.php?t=43362&page=7&p=869662&highlight=trilateration3d#post869662)
     */
    private function vector_div($v, $l)
    {
        return array($v[0]/$l, $v[1] / $l, $v[2] / $l);
    }

    /**
     * vector_unit function for trilateration
     *
     * @param float $v
     * @return array
     * @author Snuble and Harbinger (https://forums.frontier.co.uk/showthread.php?t=43362&page=7&p=869662&highlight=trilateration3d#post869662)
     */
    private function vector_unit($v)
    {
        $l = $this->vector_norm($v);

        if ($l == 0) {
            return -1;
        }
        return $this->vector_div($v, $l);
    }

    /**
     * Trilateration function for calculating 3D coordinates
     *
     * @param array $p1
     * @param array $p2
     * @param array $p3
     * @param array $p4
     * @return array $coords coordinates
     * @author Snuble and Harbinger (https://forums.frontier.co.uk/showthread.php?t=43362&page=7&p=869662&highlight=trilateration3d#post869662)
     */
    public function trilateration3d($p1, $p2, $p3, $p4)
    {
        $ex = $this->vector_unit($this->vector_diff($p2, $p1));
        $i = $this->vector_dot_product($ex, $this->vector_diff($p3, $p1));
        $ey = $this->vector_unit($this->vector_diff($this->vector_diff($p3, $p1), $this->vector_multiply($ex, $i)));
        $ez = $this->vector_cross($ex, $ey);
        $d = $this->vector_length($p2, $p1);
        $r1 = $p1[3];
        $r2 = $p2[3];
        $r3 = $p3[3];
        $r4 = $p4[3];

        if ($d - $r1 >= $r2 || $r2 >= $d + $r1) {
            return array();
        }

        $j = $this->vector_dot_product($ey, $this->vector_diff($p3, $p1));
        $x = (($r1*$r1) - ($r2*$r2) + ($d*$d)) / (2*$d);
        $y = ((($r1*$r1) - ($r3*$r3) + ($i*$i) + ($j*$j)) / (2*$j)) - (($i*$x) / $j);
        $z = $r1*$r1 - $x*$x - $y*$y;

        if ($z < 0) {
            return array();
        }

        $z1 = sqrt($z);
        $z2 = $z1 * -1;

        $result1 = $p1;
        $result1 = $this->vector_sum($result1, $this->vector_multiply($ex, $x));
        $result1 = $this->vector_sum($result1, $this->vector_multiply($ey, $y));
        $result1 = $this->vector_sum($result1, $this->vector_multiply($ez, $z1));
        $result2 = $p1;
        $result2 = $this->vector_sum($result2, $this->vector_multiply($ex, $x));
        $result2 = $this->vector_sum($result2, $this->vector_multiply($ey, $y));
        $result2 = $this->vector_sum($result2, $this->vector_multiply($ez, $z2));

        $r1 = $this->vector_length($p4, $result1);
        $r2 = $this->vector_length($p4, $result2);
        $t1 = $r1 - $r4;
        $t2 = $r2 - $r4;

        if (abs($t1) < abs($t2)) {
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
        } else {
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
}
