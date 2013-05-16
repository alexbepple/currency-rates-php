notification :off

guard 'shell' do
  watch(/.*.php$/) {|m| `phpunit --colors test/` }
end
