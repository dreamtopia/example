<?php
/*
现在这样的设计：
1.web元素中有abcdef...不定个数的选项的选择题[有可能abc3个选项，也可能abcdef6个选项]
2.php处理机制：
如果选择abc就是：2^0+2^1+2^2 = 7
如果选择ac就是：2^0 + 2^2 = 5
然后将结果入库
3.问题：
php如何处理像上面的7,5这样的库数据为abc，ac?
c的描述的话，就是:
  void func(int num)
  {
    char c = 'a';
    while (num > 0)
    {
      if ((num % 2) == 1)
      {
          printf("%c", c);
      }
      num = num / 2;
      c = c + 1;
    }
  }
  转换成php如下：
    function func($num) {
      $c = 0x41;
      while($num >0) {
          if($num %2 == 1) {
               echo chr($c);
          }
          $num = intval($num / 2);
          $c = $c + 1;
      }
  }
*/

