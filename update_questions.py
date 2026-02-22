import sys
import random
import re

random.seed(42)  # For reproducibility

filepath = 'c:/modules/mbti/mbti.model.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace 9
q9_old = "'q' => '누군가와 심하게 다투고 화가 머리끝까지 났을 때!', 'a' => ['text' => '일단 속사포처럼 내 감정과 생각을 말로 다 털어놓아야 직성이 풀린다.', 'value' => 'E'], 'b' => ['text' => '입을 닫고 혼자만의 공간에서 감정이 정리될 때까지 생각할 시간이 필요하다.', 'value' => 'I']"
q9_new = "'q' => '나에게 복잡하고 힘든 고민거리가 생겼을 때, 나는 주로 어떻게 해결할까?', 'a' => ['text' => '일단 친한 사람들에게 털어놓으며 말을 하다 보면 생각과 감정이 자연스레 정리된다.', 'value' => 'E'], 'b' => ['text' => '내 안에서 충분히 생각하고 스스로 감정을 정리한 뒤에, 비로소 남에게 이야기할 수 있다.', 'value' => 'I']"
content = content.replace(q9_old, q9_new)

# Replace 13
q13_old = "'q' => '친구들과 카페에 갈 때 당신의 좌석 선호도는?', 'a' => ['text' => '사람 구경도 하고 적당히 백색소음도 있는 넓고 뚫린 중앙 좌석.', 'value' => 'E'], 'b' => ['text' => '우리끼리만 대화할 수 있는 구석진 곳이나 칸막이 있는 아늑한 좌석.', 'value' => 'I']"
q13_new = "'q' => '아무 일정 없는 꿀 같은 주말! 나를 가장 충전시켜주는 휴식의 형태는?', 'a' => ['text' => '드라이브를 가든, 전시회를 가든, 핫플을 가든 일단 집 밖으로 나가서 새로운 공기를 마시는 것!', 'value' => 'E'], 'b' => ['text' => '밀린 예능이나 영화를 켜놓고, 맛있는 배달 음식을 시켜 먹으며 푹신한 침대와 한 몸이 되는 것!', 'value' => 'I']"
content = content.replace(q13_old, q13_new)

# Now iterate line by line to shuffle 'a' and 'b' options randomly for ALL 60 questions
lines = content.split('\n')
for i, line in enumerate(lines):
    if "'id' => " in line and "'type' => " in line and "'a' => [" in line and "'b' => [" in line:
        if random.choice([True, False]):
            # Swap 'a' and 'b' options
            # Example format: 'a' => ['text' => '...', 'value' => 'E'], 'b' => ['text' => '...', 'value' => 'I']
            # We want to swap them so 'a' becomes the 'I' option, 'b' becomes the 'E' option.
            
            # Find the parts matching 'a' => [...] and 'b' => [...]
            # We must be careful because text can contain quotes, but they are escaped or single quotes are used.
            # Using find to be safer
            a_idx = line.find("'a' => [")
            b_idx = line.find("'b' => [")
            
            if a_idx != -1 and b_idx != -1:
                # b_idx is presumably after a_idx
                # The 'a' part ends at b_idx - 2 (the comma and space)
                a_str = line[a_idx:b_idx-2]
                
                # The 'b' part ends at the end of the array definition, just before ]],
                # Actually, the line ends with ']],' usually.
                b_str = line[b_idx:]
                if b_str.endswith(']],'):
                    b_str = b_str[:-3] + ']'
                    tail = '],'
                elif b_str.endswith(']]'):
                    b_str = b_str[:-2] + ']'
                    tail = ']'
                else:
                    tail = ''
                
                # Extract inner content
                a_inner = a_str.replace("'a' => ", "")
                b_inner = b_str.replace("'b' => ", "")
                
                # Construct new
                new_a = "'a' => " + b_inner
                new_b = "'b' => " + a_inner
                
                new_line = line[:a_idx] + new_a + ", " + new_b + "]" + tail
                lines[i] = new_line

with open(filepath, 'w', encoding='utf-8') as f:
    f.write('\n'.join(lines))

print('SUCCESS')
